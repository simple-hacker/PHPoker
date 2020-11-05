<?php

namespace simplehacker\PHPoker;

use simplehacker\PHPoker\Exceptions\InvalidPlayerOperationException;

class Player
{
    /**
     * The player's hole card
     * 
     * @var array
     */
    protected $holeCards = [];

    /**
    * Assign an array of Card's to the player's hole cards
    * Each element of the array must be an instance of Card
    * 
    * @param array $cards
    * @return void
    */
    public function giveCards(array $cards): void
    {
        // Make sure each element of the array is an instanceof Card
        foreach ($cards as $card) {
            if (! ($card instanceof Card)) {
                throw new InvalidPlayerOperationException('Array must only contains instances of Card');
            }
        }

        // TODO: Check for duplicate Cards in holeCards
        $this->holeCards = [...$this->holeCards, ...$cards];  
    }

    /**
    * Assign a Card's to the player's hole cards
    * 
    * @param Card $card
    * @return void
    */
    public function giveCard(Card $card): void
    {
        $this->holeCards[] = $card;
    }

    /**
    * Returns the player's hole cards.
    * 
    * @return array
    */
    public function getHoleCards(): Array
    {
        return $this->holeCards;
    }
}