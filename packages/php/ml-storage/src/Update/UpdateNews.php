<?php
namespace EverISay\SIF\ML\Storage\Update;

class UpdateNews {
    function __construct(
        public string $assetHash,
    ) {
        $this->newsCreateTime = new \DateTimeImmutable;
    }

    public \DateTimeInterface $newsCreateTime;

    /** @var News\NewMusic[] */
    public array $newMusic = [];
}
