<?php

namespace simplehacker\PHPoker;

use simplehacker\PHPoker\Card;

class Deck
{
    public $cards = [];

    /**
     * Instantiate a deck full of cards
     *
     * @param boolean $shuffle
     */
    public function __construct($shuffle = true)
    {
        $cardValues = array_keys(Card::$values);
        $cardSuits = array_keys(Card::$suits);

        foreach($cardValues as $value) {
            foreach ($cardSuits as $suit) {
                array_push($this->cards, new Card($value, $suit));
            }
        }

        if ($shuffle) {
            shuffle($this->cards);
        }
    }
}