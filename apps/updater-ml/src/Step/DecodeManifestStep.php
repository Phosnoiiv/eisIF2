<?php
namespace EverISay\SIF\ML\Updater\Step;

use EverISay\SIF\ML\Common\Config\AbstractVersionConfig;
use EverISay\SIf\ML\Common\Proprietary\AssetHelperInterface;
use EverISay\SIF\ML\Storage\DownloadStorage;
use EverISay\SIF\ML\Updater\Exception\UpdaterException;
use EverISay\SIF\ML\Updater\Helper\TempFileHelper;
use Symfony\Component\Process\Process;

final class DecodeManifestStep {
    function __construct(
        private readonly DownloadStorage $downloadStorage,
        private readonly AbstractVersionConfig $versionConfig,
        private readonly TempFileHelper $tempFileHelper,
        private readonly AssetHelperInterface $assetHelper,
    ) {}

    public function execute(string $assetHash): void {
        $manifestName = $this->versionConfig->proprietaryConfig::ASSET_MANIFEST_NAME;
        if (!$this->downloadStorage->hasBundle($manifestName, $assetHash)) {
            throw new UpdaterException('Manifest not found');
        }
        $this->downloadManifestPath = $this->downloadStorage->getBundleLocalPath($manifestName, $assetHash);
        $this->tempRoot = $this->tempFileHelper->getPath();
        $this->process('Bundle');
    }

    private readonly string $downloadManifestPath;
    private readonly string $tempRoot;

    private function process(string $name): void {
        $decoder = new Process([env('BIN_DECODER'), 'export', $this->downloadManifestPath, $name, $this->tempRoot]);
        $decoder->mustRun();
        $data = file_get_contents($this->tempFileHelper->getPath($name));
        $data = $this->assetHelper->decryptManifest($data);
        $data = gzdecode($data);
        $serializedPath = $this->tempFileHelper->getPath() . '/' . $name . '-1';
        file_put_contents($serializedPath, $data);
    }
}
