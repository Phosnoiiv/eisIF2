<?php
namespace EverISay\SIF\ML\Storage;

use EverISay\SIF\ML\Storage\Manifest\AbstractManifestCollection;
use EverISay\SIF\ML\Storage\Manifest\BundleManifestCollection;
use EverISay\SIF\ML\Storage\Manifest\ManifestName;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

final class ManifestStorage {
    use SimpleStorageTrait {
        SimpleStorageTrait::readSimpleStorage as readSimpleData;
        SimpleStorageTrait::writeSimpleStorage as writeSimpleData;
    }

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

    private readonly Serializer $serializer;
    private function getInnerSerializer(): Serializer {
        return $this->serializer ??= $this->getSerializer();
    }

    /**
     * Get a Symfony Serializer. This function may be used by other components if needed.
     */
    public function getSerializer(?NameConverterInterface $nameConverter = null, array $additionalNormalizers = []): Serializer {
        return new Serializer(array_merge([new ArrayDenormalizer, new PropertyNormalizer(
            nameConverter: $nameConverter,
            propertyTypeExtractor: new PropertyInfoExtractor(typeExtractors: [new PhpDocExtractor, new ReflectionExtractor]),
        )], $additionalNormalizers), [new JsonEncoder(defaultContext: [
            JsonEncode::OPTIONS => JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        ])]);
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
        $data = $this->localFiles->read($this->getSavePath($assetHash, $manifestName));
        return $this->getInnerSerializer()->deserialize($data, $class, 'json');
    }

    public function loadBundleManifest(?string $assetHash = null): BundleManifestCollection {
        return $this->load($assetHash, ManifestName::Bundle, BundleManifestCollection::class);
    }

    public function save(string $assetHash, \DateTimeInterface $time, AbstractManifestCollection $collection): void {
        $manifestName = $collection->getName();
        $this->localFiles->write($this->getSavePath($assetHash, $manifestName), $this->getInnerSerializer()->serialize($collection, 'json'));
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
