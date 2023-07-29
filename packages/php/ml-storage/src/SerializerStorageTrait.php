<?php
namespace EverISay\SIF\ML\Storage;

use League\Flysystem\Filesystem;

trait SerializerStorageTrait {
    use SerializerTrait;

    abstract private function getSerializerFilesystem(): Filesystem;

    private function readSerializerStorage(string $path, string $typeName, mixed $default = null): mixed {
        if (!$this->getSerializerFilesystem()->fileExists($path)) return $default;
        return $this->deserialize($this->getSerializerFilesystem()->read($path), $typeName);
    }

    private function writeSerializerStorage(string $path, mixed $data): void {
        $this->getSerializerFilesystem()->write($path, $this->serialize($data));
    }
}
