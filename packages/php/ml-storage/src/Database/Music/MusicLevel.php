<?php
namespace EverISay\SIF\ML\Storage\Database\Music;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use EverISay\SIF\ML\Storage\Database\AbstractEntity;
use EverISay\SIF\ML\Storage\Database\DoubleIdEntityInterface;
use EverISay\SIF\ML\Storage\Database\DoubleIdEntityTrait;

#[Entity(database: 'music')]
class MusicLevel extends AbstractEntity implements DoubleIdEntityInterface {
    use DoubleIdEntityTrait;

    #[Column('integer', primary: true)]
    public int $masterMusicId;
    #[Column('integer', primary: true)]
    public int $level;

    public function getFirstId(): int {
        return $this->masterMusicId;
    }
    public function getSecondId(): int {
        return $this->level;
    }
    public function getReleaseLabelId(): int {
        return $this->masterReleaseLabelId;
    }

    public function getLevelEnum(): LiveLevel {
        return LiveLevel::from($this->level);
    }

    #[Column('integer')]
    public int $levelNumber;
    #[Column('text')]
    public string $noteDataFileName;
    #[Column('integer')]
    public int $fullCombo;
    #[Column('integer')]
    public int $beforeClimaxNotesCount;
    #[Column('float')]
    public float $scoreCoeff;
    #[Column('float')]
    public float $climaxScoreCoeff;
    #[Column('float')]
    public float $voltageIncreaseCoeff;
    #[Column('float')]
    public float $voltageDecreaseCoeff;
    #[Column('integer')]
    public int $masterReleaseLabelId;

}
