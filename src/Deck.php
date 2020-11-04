<?php

namespace simplehacker\PHPoker;

use simplehacker\PHPoker\Card;
use simplehacker\PHPoker\Exceptions\InvalidDeckOperationException;

class Deck
{
    public $cards = [];

    const CARD_LIMIT = 52;

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
                $this->addCard(new Card($value, $suit));
            }
        }

        if ($shuffle) {
            shuffle($this->cards);
        }
    }

    /**
    * Returns the number of cards in the deck
    * 
    * @return integer
    */
    public function count(): Int
    {
        return count($this->cards);
    }

    /**
    * Add a card to the deck
    * 
    * @param Card $card
    * @return void
    */
    public function addCard(Card $card)
    {
        if (count($this->cards) >= self::CARD_LIMIT) {
            throw new InvalidDeckOperationException('Deck is full');
        }
        
        $findCard = array_search($card, $this->cards);
        
        if (! $findCard) {
            array_push($this->cards, $card);
        } else {
            $cardDescription = $card->getDescription();
            throw new InvalidDeckOperationException("$cardDescription is already in deck");
        }
    }

    /**
    * Take a card from the top of the deck if null
    * or
    * Take a specific card from anywhere in the deck if found
    * 
    * @param Card $card
    * @return Card
    */
    public function takeCard(Card $card = null): Card
    {
        if (! $card) {
            return array_shift($this->cards);
        } else {
            
            $findCard = array_search($card, $this->cards);
            
            if ($findCard !== false) {
                $card = $this->cards[$findCard];
                array_splice($this->cards, $findCard, 1);
                return $card;
            } else {
                $cardDescription = $card->getDescription();
                throw new InvalidDeckOperationException("$cardDescription not found in deck");
            }
        }
    }

    /**
    * Take and return a number of cards from the deck
    * 
    * @param int $n
    * @return array
    */
    public function takeCards($n = 1): Array
    {
        $cards = [];

        if ($n < 1) {
            $n = 1;
        }

        if ($n > count($this->cards)) {
            throw new InvalidDeckOperationException("Not enough cards in deck to remove $n cards");
        }

        for ($i = 0; $i < $n; $i++) {
            $cards[] = $this->takeCard();
        }

        return $cards;
    }
}