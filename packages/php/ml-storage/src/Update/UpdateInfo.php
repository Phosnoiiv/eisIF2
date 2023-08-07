<?php
namespace EverISay\SIF\ML\Storage\Update;

class UpdateInfo {
    function __construct(
        public string $assetHash,
        public \DateTimeInterface $updateTime,
        public string $description,
        public bool $isInitial = false,
    ) {
        $this->infoCreateTime = new \DateTimeImmutable;
    }

    public \DateTimeInterface $infoCreateTime;
    public \DateTimeInterface $infoFinishTime;

    public array $databaseNewIds = [];
    public array $databaseChanges = [];
}
