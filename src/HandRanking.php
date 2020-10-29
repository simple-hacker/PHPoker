<?php

namespace simplehacker\PHPoker;

use simplehacker\PHPoker\Card;
use simplehacker\PHPoker\Exceptions\InvalidCardException;
use simplehacker\PHPoker\Exceptions\InvalidHandRankingException;

class HandRanking
{
    protected $cards = [];

    /**
    * Instantiate the Hand Ranking class
    * It accepts an array of at least five Cards
    * or a string of at least 5 cards in short values Cards
    * i.e. 'Ac2h3s4d5s'
    * 
    * @param array|string $cards
    */
    public function __construct($cards)
    {
        if (is_array($cards)) {
            $this->cards = array_map(function($card) {
                return ($card instanceof Card) ? $card : new Card($card);
            }, $cards);
        }

        if (is_string($cards)) {
            $shortCards = str_split($cards, 2);

            $this->cards = array_map(function($shortCardValue) {
                return new Card($shortCardValue);
            }, $shortCards);
        }

        // Throw error if duplicate cards are given
        $uniqueCards = array_unique($this->cards);

        if (count($uniqueCards) != count($this->cards)) {
            $duplicateCards = array_diff_assoc($this->cards, $uniqueCards);
            $invalidCards = implode(", ", $duplicateCards);
            throw new InvalidHandRankingException("Duplicate cards given: $invalidCards");
        }

        // Throw error if less than five cards given
        // Because at least five are needed to determine hand rank
        // More than five valid because we determine best five cards out of x provided
        if (count($this->cards) < 5) {
            throw new InvalidHandRankingException('Need at least 5 cards to determine hand ranking');
        }
    }

    /**
    * Returns the protected array of cards for this hand ranking 
    *
    * @return array
    */
    public function getCards(): Array
    {
        return $this->cards;
    }
}