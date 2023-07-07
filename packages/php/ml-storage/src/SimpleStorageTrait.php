<?php
namespace EverISay\SIF\ML\Storage;

use League\Flysystem\Filesystem;

trait SimpleStorageTrait {
    abstract private function getSimpleFilesystem(): Filesystem;

    private function readSimpleStorage(string $path, mixed $default = null): mixed {
        if (!$this->getSimpleFilesystem()->fileExists($path)) return $default;
        return unserialize($this->getSimpleFilesystem()->read($path));
    }

    private function writeSimpleStorage(string $path, mixed $data): void {
        $this->getSimpleFilesystem()->write($path, serialize($data));
    }
}
