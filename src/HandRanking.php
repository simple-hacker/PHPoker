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
     * The cards grouped and sorted by count of each value rank
     * 
     * @var array
     */
    protected $valueHistogram = [];

    /**
     * The cards values histogram sorted by value rank highest to lowest
     * This is needed for straights
     * 
     * @var array
     */
    protected $sortedValues = [];

    /**
     * The cards grouped and sorted by count of each suit
     * 
     * @var array
     */
    protected $suitHistogram = [];

    /**
     * If a straight is found then this will be the five key values of the straight in order
     * e.g. [13, 12, 11, 10, 9]
     * 
     * @var array
     */
    protected $foundStraight = [];

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
        // Up to eight cards are valid because we determine best five cards out of x provided
        if (count($this->cards) < 5 || count($this->cards) > 8) {
            throw new InvalidHandRankingException('Need between 5 and 8 cards to determine hand ranking');
        }

        $this->valueHistogram = $this->computeValueHistogram();
        $this->sortedValues = $this->computeSortedValues();
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
    * Group and sort the cards according to the count of each value
    * 
    * @return array
    */
    private function computeValueHistogram(): Array
    {
        $values = [];

        // Group all cards in to values
        foreach ($this->cards as $card) {
            $values[$card->getValueRank()][] = $card;
        }

        // Sort values by count of cards in each value.
        // uasort is user defined sorting function which maintains keys which are numerical value of value
        uasort($values, fn($value1, $value2) => count($value2) <=> count($value1));

        // Sort cards of each value according to their suit rank highest first
        foreach($values as $index => $value) {
            // Create copy of array to sort
            $sortedValues = $value;
            usort($sortedValues, fn($card1, $card2) => $card2->getSuitRank() <=> $card1->getSuitRank());
            // Replace value array with sortedArray at index
            $values[$index] = $sortedValues;
        }

        return $values;
    }

    /**
    * Sort the value histogram by value rank highest to lowest instead of count
    * This is needed for straights
    * 
    * @return array
    */
    private function computeSortedValues(): Array
    {
        $sortedHistogram = $this->valueHistogram;

        // Copy aces and convert rank for low straight.
        if (array_key_exists(14, $sortedHistogram)) {
            $sortedHistogram[1] = $sortedHistogram[14];
        }

        krsort($sortedHistogram, SORT_NUMERIC);

        return $sortedHistogram;
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

        // Sort cards of each suit according to their value rank highest first
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
    * Returns if the hand ranking is a four of a kind
    * Determined by if the count of the first element of value histogram is exactly four
    * and second element is one or greater
    * 
    * @return bool
    */
    public function isFourOfAKind(): Bool
    {
        $values = array_values($this->valueHistogram);
        return (count($values[0]) === 4 && count($values[1]) >= 1);
    }

    /**
    * Returns if the hand ranking is a full house
    * Determined by if the count of the first element of value histogram is exactly three
    * and second element is two or greater
    * 
    * @return bool
    */
    public function isFullHouse(): Bool
    {
        $values = array_values($this->valueHistogram);
        return (count($values[0]) === 3 && count($values[1]) >= 2);
    }

    /**
    * Returns if the hand ranking is a flush
    * Determined by if the count of the first element of suit histogram is five or greater
    * 
    * @return bool
    */
    public function isFlush(): Bool
    {
        return count(reset($this->suitHistogram)) >= 5;
    }

    /**
    * Returns if the hand ranking is a straight
    * Determined by if there are five keys of value histogram in sequential decending order (as it's sorted)
    * 
    * @return bool
    */
    public function isStraight(): Bool
    {
        // Grab the keys of the sortedValues
        $sortedValues = array_keys($this->sortedValues);

        do {
            // Take first five cards index values
            $fiveCards = array_slice($sortedValues, 0, 5);
            // Build a potential straight based off the first value of the first card
            $compareStraight = range($sortedValues[0], $sortedValues[0]-4);
            // If fiveCards is the same as the compareStraight that's been built, then a straight has been found
            if ($fiveCards === $compareStraight) {
                $this->foundStraight = $fiveCards;
                return true;
            }
            // If not sequential then remove first element and repeat while there are at least five cards
            array_splice($sortedValues, 0, 1);
        } while(count($sortedValues) > 4);

        // Return false if no straight is found after looping and checking sequential of all given cards
        return false;
    }

    /**
    * Returns if the hand ranking is a three of a kind
    * Determined by if the count of the first element of value histogram is exactly three
    * and second element and third element are exactly one.
    * 
    * @return bool
    */
    public function isThreeOfAKind(): Bool
    {
        $values = array_values($this->valueHistogram);
        return (count($values[0]) === 3 && count($values[1]) === 1 && count($values[2]) === 1);
    }

    /**
    * Returns if the hand ranking is two pairs
    * Determined by if the count of the first and second elements of value histogram are exactly two
    * and third element is one or greater
    * 
    * @return bool
    */
    public function isTwoPair(): Bool
    {
        $values = array_values($this->valueHistogram);
        return (count($values[0]) === 2 && count($values[1]) === 2 && count($values[2]) >= 1);
    }

    /**
    * Returns if the hand ranking is one pairs
    * Determined by if the count of the first element is exactly two
    * and second, third and fourth element is exactly one
    * 
    * @return bool
    */
    public function isOnePair(): Bool
    {
        $values = array_values($this->valueHistogram);
        return (count($values[0]) === 2 && count($values[1]) === 1 && count($values[2]) === 1 && count($values[3]) === 1);
    }

    /**
    * Returns if the hand ranking is one pairs
    * Determined by if the count of the each elemt is exactly one
    * 
    * @return bool
    */
    public function isHighCard(): Bool
    {
        $values = array_values($this->valueHistogram);
        return (count($values[0]) === 1 && count($values[1]) === 1 && count($values[2]) === 1 && count($values[3]) === 1 && count($values[4]) === 1);
    }
}