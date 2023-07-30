<?php
namespace EverISay\SIF\ML\Updater\Step;

use EverISay\SIF\ML\Storage\DatabaseStorage;
use EverISay\SIF\ML\Storage\Update\UpdateNews;
use EverISay\SIF\ML\Storage\UpdateStorage;

final class CreateNewsStep {
    function __construct(
        private readonly DatabaseStorage $databaseStorage,
        private readonly UpdateStorage $updateStorage,
    ) {}

    public function execute(): void {
        $infoMetadata = $this->updateStorage->readMetadata();
        $assetHash = reset($infoMetadata);
        $info = $this->updateStorage->readUpdateInfo($assetHash);
        if (null === $info) return;
        $news = new UpdateNews($info->assetHash);
        $this->updateStorage->writeUpdateNews($news);
    }
}
