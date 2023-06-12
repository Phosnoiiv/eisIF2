<?php
namespace EverISay\SIF\ML\Updater\Step;

use EverISay\SIF\ML\Common\Config\AbstractVersionConfig;
use EverISay\SIF\ML\Storage\DownloadStorage;
use EverISay\SIF\ML\Updater\Helper\NetworkHelper;

final class DownloadManifestStep {
    function __construct(
        private readonly DownloadStorage $storage,
        private readonly AbstractVersionConfig $versionConfig,
        private readonly NetworkHelper $networkHelper,
    ) {}

    public function execute(string $assetHash): void {
        $name = $this->versionConfig->proprietaryConfig::ASSET_MANIFEST_NAME;
        if ($this->storage->hasBundle($name, $assetHash)) return;
        $content = $this->networkHelper->getBundle($name, $assetHash);
        $this->storage->writeBundle($name, $assetHash, $content);
    }
}
