<?php
namespace EverISay\SIF\ML\Storage\Update\News;

use EverISay\SIF\ML\Storage\Database\Card\CardType;
use EverISay\SIF\ML\Storage\Database\Music\Live;
use EverISay\SIF\ML\Storage\Database\Music\Music;
use EverISay\SIF\ML\Storage\Database\Music\MusicLevel;

class NewMusic {
    private function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $artist,
        public readonly string $detailInfo,
        public readonly string $dictionaryReference,
    ) {}

    final public static function create(Music $music): self {
        return new self($music->id, $music->name, $music->artist, $music->detailInfo, $music->dictionaryReference);
    }

    public CardType $type;
    /** @var int[] */
    public array $levelNumbers;
    /** @var int[] */
    public array $fullCombos;

    /**
     * @return $this
     */
    public function setLive(?Live $live): static {
        if (null === $live) return $this;
        $this->type = $live->type;
        return $this;
    }

    /**
     * @return $this
     */
    public function addMusicLevel(?MusicLevel $level): static {
        if (null === $level) return $this;
        $this->levelNumbers[$level->level] = $level->levelNumber;
        $this->fullCombos  [$level->level] = $level->fullCombo;
        return $this;
    }

    /**
     * @param MusicLevel[] $levels
     * @return $this
    */
    public function addMusicLevels(array $levels): static {
        foreach ($levels as $level) {
            $this->addMusicLevel($level);
        }
        return $this;
    }
}
