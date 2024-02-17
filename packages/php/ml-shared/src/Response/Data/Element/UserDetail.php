<?php
namespace EverISay\SIF\ML\Shared\Response\Data\Element;

class UserDetail {
    public User $user;
    public Card $favoriteCard;
    // public Card $guestSmileCard;
    // public Card $guestCoolCard;
    // public Card $guestPureCard;
    // /** @var Group[] */
    // public array $groupList;
    // public DeckDetail $mainDeckDetail;
    // public LiveDataSummary $liveDataSummary;
    /** @var int[] */
    public array $masterTitleIds;
}
