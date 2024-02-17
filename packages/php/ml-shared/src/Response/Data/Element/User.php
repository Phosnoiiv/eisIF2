<?php
namespace EverISay\SIF\ML\Shared\Response\Data\Element;

class User {
    public int $id;
    public string $name;
    public string $comment;
    public int $exp;
    public int $level;
    public int $mainDeckSlot;
    public int $favoriteMasterCardId;
    public int $favoriteCardEvolve;
    public int $guestSmileMasterCardId;
    public int $guestCoolMasterCardId;
    public int $guestPureMasterCardId;
    /** @var int[] */
    public array $masterTitleIds;
    /** @var int[] */
    public array $profileSettings;
    public int $lastLoginTime;
    public int $sifUserId;
    public int $ssUserId;
    public string $birthday;
}
