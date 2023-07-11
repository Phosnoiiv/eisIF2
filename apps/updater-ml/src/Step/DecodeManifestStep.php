<?php
namespace EverISay\SIF\ML\Updater\Step;

use EverISay\SIF\ML\Common\Config\AbstractVersionConfig;
use EverISay\SIf\ML\Common\Proprietary\AssetHelperInterface;
use EverISay\SIF\ML\Storage\DownloadStorage;
use EverISay\SIF\ML\Storage\Manifest\BundleManifestCollection;
use EverISay\SIF\ML\Storage\Manifest\ManifestName;
use EverISay\SIF\ML\Storage\ManifestStorage;
use EverISay\SIF\ML\Updater\Exception\UpdaterException;
use EverISay\SIF\ML\Updater\Helper\TempFileHelper;
use EverISay\SIF\ML\Updater\ManifestPropertyNameConverter;
use Symfony\Component\Process\Process;
use Symfony\Component\Serializer\Serializer;

final class DecodeManifestStep {
    function __construct(
        private readonly DownloadStorage $downloadStorage,
        private readonly ManifestStorage $manifestStorage,
        private readonly AbstractVersionConfig $versionConfig,
        private readonly TempFileHelper $tempFileHelper,
        private readonly AssetHelperInterface $assetHelper,
    ) {}

    private readonly string $assetHash;
    private readonly \DateTimeInterface $time;

    public function execute(string $assetHash, \DateTimeInterface $time): void {
        $this->assetHash = $assetHash;
        $this->time = $time;
        $manifestName = $this->versionConfig->proprietaryConfig::ASSET_MANIFEST_NAME;
        if (!$this->downloadStorage->hasBundle($manifestName, $assetHash)) {
            throw new UpdaterException('Manifest not found');
        }
        $this->downloadManifestPath = $this->downloadStorage->getBundleLocalPath($manifestName, $assetHash);
        $this->tempRoot = $this->tempFileHelper->getPath();
        $this->serializer = $this->manifestStorage->getSerializer(new ManifestPropertyNameConverter);
        $this->process(ManifestName::Bundle, BundleManifestCollection::class);
    }

    private readonly string $downloadManifestPath;
    private readonly string $tempRoot;
    private readonly Serializer $serializer;

    private function process(ManifestName $manifestName, string $manifestCollectionClassName): void {
        if ($this->manifestStorage->hasManifest($this->assetHash, $manifestName)) {
            $this->manifestStorage->saveMetadata($this->assetHash, $this->time, $manifestName);
            return;
        }
        $name = $manifestName->name;
        $decoder = new Process([env('BIN_DECODER'), 'export', $this->downloadManifestPath, $name, $this->tempRoot]);
        $decoder->mustRun();
        $data = file_get_contents($this->tempFileHelper->getPath($name));
        $data = $this->assetHelper->decryptManifest($data);
        $data = gzdecode($data);
        $serializedPath = $this->tempFileHelper->getPath() . '/' . $name . '-1';
        file_put_contents($serializedPath, $data);
        $deserializedPath = $this->tempFileHelper->getPath($name . '.json');
        $decoder = new Process([env('BIN_DECODER'), 'deserialize', $serializedPath, $deserializedPath]);
        $decoder->mustRun();
        $data = file_get_contents($deserializedPath);
        $data = $this->serializer->deserialize($data, $manifestCollectionClassName, 'json');
        $this->manifestStorage->save($this->assetHash, $this->time, $data);
    }
}
