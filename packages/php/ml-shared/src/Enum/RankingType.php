<?php
namespace EverISay\SIF\ML\Shared\Enum;

enum RankingType: int {
    case None = 0;
    case Member = 1;
    case Point = 2;
    case Score = 3;
    case Emotional = 4;
}
