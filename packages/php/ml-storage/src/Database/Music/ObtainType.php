<?php
namespace EverISay\SIF\ML\Storage\Database\Music;

enum ObtainType: int {
    case None = 0;
    case Shop = 1;
    case Mission = 2;
    case Event = 3;
    case Story = 4;
    case PlayerRank = 5;
}
