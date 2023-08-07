<?php
namespace EverISay\SIF\ML\Storage\Database\Character;

enum BandCategory: int {
    case None = 0;
    case Muse = 1;
    case Aqours = 2;
    case Nijigaku = 3;
    case Liella = 4;
    case Hasunosora = 5;
    case Other = 6;

    /** @since 2.0.0 (Game 1.3.0) */
    case Yohane = 7;
    /** @since 2.0.0 (Game 1.3.0) */
    case Num = 8;
}
