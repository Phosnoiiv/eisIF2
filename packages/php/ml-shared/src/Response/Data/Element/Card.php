<?php
namespace EverISay\SIF\ML\Shared\Response\Data\Element;

class Card {
    public int $id;
    public int $masterCardId;
    public int $exp;
    public int $skillExp;
    /** @var EvolveInfo[] */
    public array $evolve;
    /** @var int[] */
    public array $episode;
    public int $createdDateTime;
}
