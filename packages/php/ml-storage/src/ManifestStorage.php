<?php
namespace EverISay\SIF\ML\Storage;

use EverISay\SIF\ML\Storage\Manifest\AbstractManifestCollection;
use EverISay\SIF\ML\Storage\Manifest\BundleManifestCollection;
use EverISay\SIF\ML\Storage\Manifest\ManifestName;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

final class ManifestStorage {
    use SimpleStorageTrait {
        SimpleStorageTrait::readSimpleStorage as readSimpleData;
        SimpleStorageTrait::writeSimpleStorage as writeSimpleData;
    }
    use SerializerStorageTrait;

    function __construct(
        private readonly string $localPath,
    ) {
        $localAdapter = new LocalFilesystemAdapter($localPath);
        $this->localFiles = new Filesystem($localAdapter);
    }

    private readonly Filesystem $localFiles;

    private function getSimpleFilesystem(): Filesystem {
        return $this->localFiles;
    }
    private function getSerializerFilesystem(): Filesystem {
        return $this->localFiles;
    }

    private function getMetadataPath(ManifestName $manifestName): string {
        return $manifestName->name . '.metadata.txt';
    }

    private function readMetadata(ManifestName $manifestName): array {
        return $this->readSimpleData($this->getMetadataPath($manifestName), []);
    }

    private function writeMetadata(ManifestName $manifestName, array $data): void {
        $this->writeSimpleData($this->getMetadataPath($manifestName), $data);
    }

    private function getLatestHashPath(ManifestName $manifestName): string {
        return $manifestName->name . '.latest.txt';
    }

    public function readLatestHash(ManifestName $manifestName): ?string {
        return $this->readSimpleData($this->getLatestHashPath($manifestName));
    }

    private function writeLatestHash(ManifestName $manifestName, string $assetHash): void {
        $this->writeSimpleData($this->getLatestHashPath($manifestName), $assetHash);
    }

    private function getSavePath(string $assetHash, ManifestName $manifestName): string {
        return $manifestName->name . '/' . $assetHash . '.json';
    }

    public function hasManifest(string $assetHash, ManifestName $manifestName): bool {
        return $this->localFiles->fileExists($this->getSavePath($assetHash, $manifestName));
    }

    private function load(?string $assetHash, ManifestName $manifestName, string $class): AbstractManifestCollection {
        if (null === $assetHash) {
            $assetHash = $this->readLatestHash($manifestName);
        }
        return $this->readSerializerStorage($this->getSavePath($assetHash, $manifestName), $class);
    }

    public function loadBundleManifest(?string $assetHash = null): BundleManifestCollection {
        return $this->load($assetHash, ManifestName::Bundle, BundleManifestCollection::class);
    }

    public function save(string $assetHash, \DateTimeInterface $time, AbstractManifestCollection $collection): void {
        $manifestName = $collection->getName();
        $this->writeSerializerStorage($this->getSavePath($assetHash, $manifestName), $collection);
        $this->saveMetadata($assetHash, $time, $manifestName);
    }

    public function saveMetadata(string $assetHash, \DateTimeInterface $time, ManifestName $manifestName): void {
        $metadata = $this->readMetadata($manifestName);
        $latestHash = $this->readLatestHash($manifestName);
        $timestamp = $time->getTimestamp();
        if (null === $latestHash || $timestamp > $metadata[$latestHash]) {
            $this->writeLatestHash($manifestName, $assetHash);
        }
        $metadata[$assetHash] = $timestamp;
        arsort($metadata);
        $this->writeMetadata($manifestName, $metadata);
    }
}
