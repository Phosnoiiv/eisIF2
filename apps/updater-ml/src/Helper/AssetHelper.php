<?php
namespace EverISay\SIF\ML\Updater\Helper;

use EverISay\SIF\ML\Storage\DownloadStorage;
use EverISay\SIF\ML\Storage\Manifest\BundleManifestCollection;
use Symfony\Component\Process\Process;

final class AssetHelper {
    function __construct(
        private readonly DownloadStorage $downloadStorage,
        private readonly NetworkHelper $networkHelper,
        private readonly TempFileHelper $tempFileHelper,
    ) {}

    public function getBundleAsset(BundleManifestCollection $collection, string $assetName): ?string {
        $manifest = $collection->findManifestByAssetName($assetName);
        if (null === $manifest) return null;
        if (!$this->downloadStorage->hasBundle($manifest->name, $manifest->hash)) {
            $content = $this->networkHelper->getBundle($manifest->name, $manifest->hash);
            $this->downloadStorage->writeBundle($manifest->name, $manifest->hash, $content);
        }
        $bundlePath = $this->downloadStorage->getBundleLocalPath($manifest->name, $manifest->hash);
        preg_match('/([[:alnum:]_]+)(\.[[:alnum:]]+)?$/', $assetName, $matches);
        $assetShortName = $matches[1];
        $decoder = new Process([env('BIN_DECODER'), 'export', $bundlePath, $assetShortName, $this->tempFileHelper->getPath()]);
        $decoder->mustRun();
        return file_get_contents($this->tempFileHelper->getPath() . '/' . $assetShortName);
    }
}
