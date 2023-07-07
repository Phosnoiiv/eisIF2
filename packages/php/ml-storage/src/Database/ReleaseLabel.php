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

    #[Column('text')]
    public string $description;
    #[Column('integer')]
    public int $releaseStatus;
    #[Column('text')]
    public string $scope;
    #[Column('text')]
    public string $openedAt;
    #[Column('text')]
    public string $closedAt;
}
