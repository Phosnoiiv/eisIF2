<?php
namespace EverISay\SIF\ML\Storage;

use EverISay\SIF\ML\Storage\Interaction\Ranking;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

final class InteractionStorage {
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

    /**
     * @param Ranking[] $rankings
     */
    public function writeRankings(int $eventId, array $rankings): void {
        $start = $rankings[0]->rank;
        $this->writeSerializerStorage($eventId . '_' . $start . '_' . time() . '.json', $rankings);
    }
}
