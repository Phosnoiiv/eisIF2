<?php
namespace EverISay\SIF\ML\Storage\Database;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

#[Entity(database: 'game')]
class ReleaseLabel extends AbstractEntity implements SingleIdEntityInterface {
    #[Column('primary')]
    public int $id;

    public function getId(): int {
        return $this->id;
    }
    public function getReleaseLabelId(): int {
        return $this->id;
    }

    #[Column('text')]
    public string $description;
    #[Column('integer')]
    public int $releaseStatus;
    #[Column('text')]
    public string $scope;
    #[Column('datetime', nullable: true)]
    public ?\DateTimeInterface $openedAt;
    #[Column('datetime', nullable: true)]
    public ?\DateTimeInterface $closedAt;
}
