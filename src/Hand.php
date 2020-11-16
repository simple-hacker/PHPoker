<?php

namespace simplehacker\PHPoker;

use simplehacker\PHPoker\Card;
use simplehacker\PHPoker\Exceptions\InvalidHandException;

abstract class Hand
{     
    /**
    * The cards given for the hand ranking
    * 
    * @var array
    */
    protected $cards = [];

    /**
    * The best five cards given for the hand ranking
    * 
    * @var array
    */
    protected $hand = [];

    /**
    * The hand ranking rank
    * e.g. Royal Flush = 10, Three of a Kind = 4
    * Used when compairing two hands together to determine winner
    * 
    * @var integer
    */
    protected $handRank = 0;

    /**
    * The hand ranking value
    * Used when comparing hands together
    * See computeHandValues for how this is calculated
    * 
    * @var integer
    */
    protected $handValue = 0;

    /**
    * The hand ranking type value
    * Used when working out if we need to include kickers in a description
    * See computeHandValues for how this is calculated
    * 
    * @var integer
    */
    protected $handValueWithoutKickers = 0;

    /**
    * If the hand was determined by kickers, when comparing to another hand, then set to true
    * Used when generating a description to include kicker information
    * 
    * @var bool
    */
    public $determinedByKickers = false;
    
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
    * The hand ranking short description
    * e.g. Kh9c6h4s3d
    * 
    * @var string
    */
    protected $shortDescription = '';

    /**
    * The hand ranking description
    * e.g. Four of a Kind, Jacks
    * 
    * @var string
    */
    protected $description = '';

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
    * Returns the protected array of best hand cards 
    *
    * @return array
    */
    public function getHand(): Array
    {
        return $this->hand;
    }

    /**
    * Returns short values description of the best hand found
    * e.g. Kh9c6h4s3d
    *
    * @return string
    */
    public function getShortDescription(): String
    {
        return $this->shortDescription;
    }

    /**
    * Returns the hand ranking rank
    *
    * @return integer
    */
    public function getHandRank(): Int
    {
        return $this->handRank;
    }

    /**
    * Returns the hand's value
    *
    * @return integer
    */
    public function getHandValue(): Int
    {
        return $this->handValue;
    }

    /**
    * Returns the hand type's value
    *
    * @return integer
    */
    public function getHandValueWithoutKickers(): Int
    {
        return $this->handValueWithoutKickers;
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