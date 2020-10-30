<?php

namespace simplehacker\PHPoker;

use phpDocumentor\Reflection\Types\Boolean;
use simplehacker\PHPoker\Card;
use simplehacker\PHPoker\Exceptions\InvalidCardException;
use simplehacker\PHPoker\Exceptions\InvalidHandRankingException;

class HandRanking
{
    /**
     * The cards given for the hand ranking
     * 
     * @var array
     */
    protected $cards = [];

    /**
     * The cards sorted grouped and sorted by value
     * 
     * @var array
     */
    protected $valueHistogram = [];

    /**
     * The cards sorted grouped and sorted by suit
     * 
     * @var array
     */
    protected $suitHistogram = [];

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

        // Throw error if less than five cards given because at least five are needed to determine hand rank
        // More than five valid because we determine best five cards out of x provided
        if (count($this->cards) < 5) {
            throw new InvalidHandRankingException('Need at least 5 cards to determine hand ranking');
        }

        $this->valueHistogram = $this->computeValueHistogram();
        $this->suitHistogram = $this->computeSuitHistogram();
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

    /**
    * Returns the protected array of cards grouped and sorted by value
    *
    * @return array
    */

    public function getValueHistogram(): Array
    {
        return $this->valueHistogram;
    }

    /**
    * Returns the protected array of cards grouped and sorted by suits
    *
    * @return array
    */
    public function getSuitHistogram(): Array
    {
        return $this->suitHistogram;
    }

    /**
    * Group and sort the cards according to their values
    * 
    * @return array
    */
    private function computeValueHistogram(): Array
    {
        return [];
    }

    /**
    * Group and sort the cards according to their suits
    * Group the cards in to suits.
    * Sort all suits by the count of cards in each suit.
    * Sort cards in each suit according to their value.
    * This is so if there is a suit with 5 cards, then a flush exists
    * and we want the flush sorted according to their value so we know the highest flush value
    * 
    * @return array
    */
    private function computeSuitHistogram(): Array
    {
        $suits = [];

        // Group all cards in to suits
        foreach ($this->cards as $card) {
            $suits[$card->getSuitRank()][] = $card;
        }

        // Sort suits by count of cards in each suits.
        // uasort is user defined sorting function which maintains keys which are numerical value of suit
        uasort($suits, fn($suit1, $suit2) => count($suit2) <=> count($suit1));

        // Sort cards of each suit according to their value
        foreach($suits as $index => $suit) {
            // Create copy of array to sort
            $sortedValues = $suit;
            usort($sortedValues, fn($card1, $card2) => $card2->getValueRank() <=> $card1->getValueRank());
            // Replace suit array with sortedArray at index
            $suits[$index] = $sortedValues;
        }

        return $suits;
    }

    /**
    * Returns if the hand ranking is a flush
    * Determined if the count of the first element of suit histogram is five or greater
    * 
    * @return bool
    */
    public function isFlush(): Bool
    {
        return count(reset($this->suitHistogram)) >= 5;
    }
}