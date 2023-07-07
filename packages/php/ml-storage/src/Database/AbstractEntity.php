<?php
namespace EverISay\SIF\ML\Storage\Database;

abstract class AbstractEntity {
    abstract public function getId(): int|string;
}
