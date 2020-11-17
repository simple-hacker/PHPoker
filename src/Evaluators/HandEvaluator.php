<?php

namespace simplehacker\PHPoker\Evaluators;

use simplehacker\PHPoker\Card;
use simplehacker\PHPoker\Exceptions\InvalidHandException;
use simplehacker\PHPoker\Traits\Hand;

abstract class HandEvaluator
{   
    use Hand;

    /**
    * The cards given for the hand ranking
    * 
    * @var array
    */
    protected $cards = [];

    /**
    * Minimum number of cards to make a hand
    * 
    * @var integer
    */
    protected $minCards = 5;

    /**
    * Maximum number of cards to make a hand
    * 
    * @var integer
    */
    protected $maxCards = 8;

    /**
    * The index for a low Ace
    * 
    * @var integer
    */
    protected $lowAceValue = 1;

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
    * Validate Cards
    * Used when instantiating different Hands
    * It accepts an array of at least five Cards
    * or a string of at least 5 cards in short values Cards
    * i.e. 'Ac2h3s4d5s'
    * 
    * @param array|string $cards
    */
    protected function validateCards($cards)
    {
        $this->cards = Card::convertToCards($cards);

        // Throw error if duplicate cards are given
        $uniqueCards = array_unique($this->cards);

        if (count($uniqueCards) != count($this->cards)) {
            $duplicateCards = array_diff_assoc($this->cards, $uniqueCards);
            $invalidCards = implode(", ", $duplicateCards); // Uses magic __toString for short card description
            throw new InvalidHandException("Duplicate cards given: $invalidCards");
        }

        // Throw error if less than five cards given because at least five are needed to determine hand rank
        // Up to eight cards are valid because we determine best five cards out of x provided
        if (count($this->cards) < $this->minCards || count($this->cards) > $this->maxCards) {
            throw new InvalidHandException('Need between ' . $this->minCards . ' and ' . $this->maxCards . ' cards to determine hand ranking');
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

    /**
    * Group and sort the cards according to the count of each value
    * 
    * @return array
    */
    protected function generateValueHistogram(): Array
    {
        $values = [];

        // Group all cards in to values
        foreach ($this->cards as $card) {
            $values[$card->getValueRank()][] = $card;
        }

        // Sort values by count of cards in each value.
        // uasort is user defined sorting function which maintains keys which are numerical value of value
        uasort($values, function($value1, $value2) {
            // If count is the same, sort by card value rank
            // Else sort by number of cards for value
            return (count($value2) === count($value1))
                        ? $value2[0]->getValueRank() <=> $value1[0]->getValueRank()
                        : count($value2) <=> count($value1);
        });

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
    protected function sortValueHistogramAccordingToValue(): Array
    {
        $sortedHistogram = $this->valueHistogram;

        // Copy aces and convert rank for low straight.
        if (array_key_exists(14, $sortedHistogram)) {
            $sortedHistogram[$this->lowAceValue] = $sortedHistogram[14];
        }

        krsort($sortedHistogram, SORT_NUMERIC);

        return $sortedHistogram;
    }
}