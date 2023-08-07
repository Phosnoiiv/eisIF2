<?php
namespace EverISay\SIF\ML\Storage\Database\Music;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use EverISay\SIF\ML\Storage\Database\AbstractEntity;
use EverISay\SIF\ML\Storage\Database\Character\BandCategory;
use EverISay\SIF\ML\Storage\Database\SingleIdEntityInterface;

#[Entity(database: 'music')]
class Music extends AbstractEntity implements SingleIdEntityInterface {
    #[Column('primary')]
    public int $id;

    public function getId(): int {
        return $this->id;
    }
    public function getReleaseLabelId(): int {
        return $this->masterReleaseLabelId;
    }

    #[Column('text')]
    public string $name;
    #[Column('text')]
    public string $shortName;
    #[Column('text')]
    public string $kana;
    #[Column('text')]
    public string $artist;
    #[Column('text')]
    public string $detailInfo;
    #[Column('text')]
    public string $dictionaryReference;
    #[Column('text')]
    public string $dictionaryComment;
    #[Column('integer', typecast: BandCategory::class)]
    public BandCategory $bandCategory;
    #[Column('integer')]
    public int $masterGroupId;
    #[Column('text')]
    public string $jacketImageName;
    #[Column('integer')]
    public int $masterBgmId;
    #[Column('integer')]
    public int $previewMasterBgmId;
    #[Column('integer')]
    public int $locked;
    #[Column('integer', typecast: ObtainType::class)]
    public ObtainType $obtainType;
    #[Column('text')]
    public string $releaseDateTime;
    #[Column('integer')]
    public int $masterReleaseLabelId;
}
