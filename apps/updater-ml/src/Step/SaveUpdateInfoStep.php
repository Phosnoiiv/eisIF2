<?php
namespace EverISay\SIF\ML\Updater\Step;

use EverISay\SIF\ML\Storage\Update\UpdateInfo;
use EverISay\SIF\ML\Storage\UpdateStorage;

final class SaveUpdateInfoStep {
    function __construct(
        private readonly UpdateStorage $updateStorage,
    ) {}

    public function execute(UpdateInfo $updateInfo): void {
        $updateInfo->infoFinishTime = new \DateTimeImmutable;
        $this->updateStorage->writeUpdateInfo($updateInfo);
    }
}
