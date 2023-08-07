<?php
namespace EverISay\SIF\ML\Storage\Database\Music;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use EverISay\SIF\ML\Storage\Database\AbstractEntity;
use EverISay\SIF\ML\Storage\Database\Card\CardType;
use EverISay\SIF\ML\Storage\Database\SingleIdEntityInterface;

#[Entity(database: 'music')]
class Live extends AbstractEntity implements SingleIdEntityInterface {
    #[Column('primary')]
    public int $id;

    public function getId(): int {
        return $this->id;
    }
    public function getReleaseLabelId(): int {
        return $this->masterReleaseLabelId;
    }

    #[Column('integer')]
    public int $masterMusicId;
    #[Column('integer', typecast: CardType::class)]
    public CardType $type;
    #[Column('integer')]
    public int $scoreC;
    #[Column('integer')]
    public int $scoreB;
    #[Column('integer')]
    public int $scoreA;
    #[Column('integer')]
    public int $scoreS;
    #[Column('integer')]
    public int $multiScoreC;
    #[Column('integer')]
    public int $multiScoreB;
    #[Column('integer')]
    public int $multiScoreA;
    #[Column('integer')]
    public int $multiScoreS;
    #[Column('integer')]
    public int $liveEffectValueId;
    #[Column('integer')]
    public int $bpm;
    #[Column('float')]
    public float $startWait;
    #[Column('float')]
    public float $endWait;
    #[Column('integer')]
    public int $masterLiveRewardSettingId;
    #[Column('integer')]
    public int $liveBgMovieMasterId;
    #[Column('text')]
    public string $rehearsalImagePath;
    #[Column('integer')]
    public int $priority;
    #[Column('integer')]
    public int $campaignFlag;
    #[Column('integer')]
    public int $masterReleaseLabelId;
}
