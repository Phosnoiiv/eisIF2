<?php
namespace EverISay\SIF\Backend\Domain\Feed;

class UpdateItem {
    function __construct(
        public string $title,
    ) {
        $this->createTime = new \DateTimeImmutable;
    }

    public \DateTimeInterface $createTime;

    public string $content = '';
}
