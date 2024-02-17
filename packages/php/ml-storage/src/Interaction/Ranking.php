<?php
namespace EverISay\SIF\ML\Storage\Interaction;

class Ranking {
    function __construct(
        public int $rank,
        public int $starLevel,
        public int $score,
        public int $userId,
        public string $userName,
        public string $userComment,
        public int $userExp,
        public int $favoriteCardId,
        public int $favoriteCardEvolve,
        public int $favoriteCardExp,
        public int $favoriteCardSkillExp,
        public int $favoriteCardCreateTime,
        public int $guestSmileCardId,
        public int $guestCoolCardId,
        public int $guestPureCardId,
        public int $titleId,
        public int $lastLoginTime,
    ) {}
}
