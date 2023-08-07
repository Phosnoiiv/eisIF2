<?php
namespace EverISay\SIF\ML\Storage\Database\Card;

enum CardType: int {
    case None = 0;
    case Smile = 1;
    case Pure = 2;
    case Cool = 3;
    case Skill = 4;
}
