<?php

namespace simplehacker\PHPoker;

use simplehacker\PHPoker\Hand;
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
     * The player's HandRanking class object
     * 
     * @var HandRanking
     */
    protected $hand;

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
                throw new InvalidPlayerOperationException('Array must only contain instances of Card');
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

    /**
    * Returns the player's number of hole cards.
    * 
    * @return integer
    */
    public function getHoleCardsCount(): Int
    {
        return count($this->holeCards);
    }

    /**
    * Sets the player's Hand class object
    * 
    * @return void
    */
    public function setHand(Hand $hand): void
    {
        $this->hand = $hand;
    }

    /**
    * Returns the player's Hand class object
    * 
    * @return Hand
    */
    public function getHand(): Hand
    {
        if (! $this->hand) {
            throw new InvalidPlayerOperationException('Hand ranking has not been generated yet');
        }

        return $this->hand;
    }
}