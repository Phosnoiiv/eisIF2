<?php
namespace EverISay\SIF\ML\Updater\Step;

use EverISay\SIF\ML\Storage\Database\Music\Live;
use EverISay\SIF\ML\Storage\Database\Music\Music;
use EverISay\SIF\ML\Storage\Database\Music\MusicLevel;
use EverISay\SIF\ML\Storage\DatabaseStorage;
use EverISay\SIF\ML\Storage\Update\News\NewMusic;
use EverISay\SIF\ML\Storage\Update\UpdateNews;
use EverISay\SIF\ML\Storage\UpdateStorage;

final class CreateNewsStep {
    function __construct(
        private readonly DatabaseStorage $databaseStorage,
        private readonly UpdateStorage $updateStorage,
    ) {}

    public function execute(): void {
        $infoMetadata = $this->updateStorage->readMetadata();
        $assetHash = reset($infoMetadata);
        $info = $this->updateStorage->readUpdateInfo($assetHash);
        if (null === $info) return;
        $news = new UpdateNews($info->assetHash);

        // New Music
        foreach ($info->databaseNewIds[Music::class] ?? [] as $id) {
            $music = $this->databaseStorage->getEntityById(Music::class, $id);
            $newMusic = NewMusic::create($music)
                ->setLive($this->databaseStorage->searchEntity(Live::class, ['masterMusicId' => $id]))
                ->addMusicLevels($this->databaseStorage->searchEntities(MusicLevel::class, ['masterMusicId' => $id]));
            $news->newMusic[] = $newMusic;
        }

        $this->updateStorage->writeUpdateNews($news);
    }
}
