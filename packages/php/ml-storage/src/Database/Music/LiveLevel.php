<?php
namespace EverISay\SIF\ML\Storage\Database\Music;

enum LiveLevel: int {
    case None = 0;
    case Normal = 1;
    case Hard = 2;
    case Expert = 3;
    case Master = 4;
}
