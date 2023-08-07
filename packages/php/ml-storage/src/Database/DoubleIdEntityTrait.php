<?php
namespace EverISay\SIF\ML\Storage\Database;

trait DoubleIdEntityTrait {
    abstract public function getFirstId(): int;
    abstract public function getSecondId(): int;

    public function getId(): string {
        return $this->getFirstId() . '_' . $this->getSecondId();
    }
}
