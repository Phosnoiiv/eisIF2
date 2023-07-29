<?php
namespace EverISay\SIF\ML\Storage;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

final class DownloadStorage {
    function __construct(
        private readonly string $localPath,
    ) {
        $localAdapter = new LocalFilesystemAdapter($localPath);
        $this->filesystem = new Filesystem($localAdapter);
    }

    private readonly Filesystem $filesystem;

    private function getPath(string $name, string $hash, string $extension): string {
        return substr($name, 0, 1) . '/' . substr($name, 0, 2) . "/{$name}_$hash.$extension";
    }

    private function getLocalPath(string $name, string $hash, string $extension): string {
        return $this->localPath . '/' . $this->getPath($name, $hash, $extension);
    }

    private function getBundlePath(string $name, string $hash): string {
        return $this->getPath($name, $hash, 'unity3d');
    }

    public function getBundleLocalPath(string $name, string $hash): string {
        return $this->getLocalPath($name, $hash, 'unity3d');
    }

    public function hasBundle(string $name, string $hash): bool {
        return $this->filesystem->fileExists($this->getBundlePath($name, $hash));
    }

    public function readBundle(string $name, string $hash): string {
        return $this->filesystem->read($this->getBundlePath($name, $hash));
    }

    public function writeBundle(string $name, string $hash, string $content): void {
        $this->filesystem->write($this->getBundlePath($name, $hash), $content);
    }
}
