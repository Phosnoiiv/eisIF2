<?php
namespace EverISay\SIF\ML\Storage;

use EverISay\SIF\ML\Storage\Update\UpdateInfo;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

final class UpdateStorage {
    use SimpleStorageTrait, SerializerStorageTrait;

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
    private function defineSerializerAdditionalNormalizers(): array {
        return [new DateTimeNormalizer([DateTimeNormalizer::TIMEZONE_KEY => '+0800'])];
    }

    private const PATH_METADATA = 'metadata.txt';
    private const PATH_INFO = 'info/%1$s.json';

    public function readMetadata(): array {
        return $this->readSimpleStorage(self::PATH_METADATA, []);
    }

    private function getInfoPath(string $assetHash): string {
        return sprintf(self::PATH_INFO, $assetHash);
    }

    public function readUpdateInfo(string $assetHash): ?UpdateInfo {
        return $this->readSerializerStorage($this->getInfoPath($assetHash), UpdateInfo::class);
    }

    public function writeUpdateInfo(UpdateInfo $info): void {
        $metadata = $this->readMetadata();
        $this->writeSerializerStorage($this->getInfoPath($info->assetHash), $info);
        $metadata[$info->updateTime->getTimestamp()] = $info->assetHash;
        krsort($metadata);
        $this->writeSimpleStorage(self::PATH_METADATA, $metadata);
    }
}
