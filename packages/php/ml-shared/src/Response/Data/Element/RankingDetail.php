<?php
namespace EverISay\SIF\ML\Shared\Response\Data\Element;

class RankingDetail {
    public int $rank;
    public UserDetail $userDetail;
    public int $score;
    // /** @var ScoreDetail[] */
    // public array $scoreDetailList;
    public int $starLevel;
}
