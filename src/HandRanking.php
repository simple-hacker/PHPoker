<?php

namespace simplehacker\PHPoker;

use simplehacker\PHPoker\Card;
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
    protected $rank = 0;

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

        $this->computeBestHand();
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
    * Returns the description of the best hand found
    * e.g. Four of a Kind, Jacks
    *
    * @return string
    */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
    * Returns the hand ranking rank
    *
    * @return integer
    */
    public function getRank(): Int
    {
        return $this->rank;
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
    * Gets the best five cards of given cards
    * Assigns a hand rank value to compare with other hands
    * Updates hand descriptions
    * 
    * @return void
    */
    private function computeBestHand()
    {
        if ($this->isRoyalFlush())
        {
            $this->hand = array_slice(reset($this->suitHistogram), 0, 5);
            $this->rank = 10;
            $this->description = 'Royal Flush, ' . $this->hand[0]->getValue() . ' to ' . $this->hand[4]->getValue() . ' of ' . $this->hand[0]->getSuit();
        }
        elseif ($this->isStraightFlush())
        {
            $this->hand = array_slice(reset($this->suitHistogram), 0, 5);
            $this->rank = 9;
            $this->description = 'Straight Flush, ' . $this->hand[0]->getValue() . ' to ' . $this->hand[4]->getValue() . ' of ' . $this->hand[0]->getSuit();
        }
        elseif ($this->isFourOfAKind())
        {
            $fourOfAKind = array_slice(reset($this->valueHistogram), 0, 4);
            $valueHistogramKeys = array_keys($this->valueHistogram);
            $notIncluding = array_splice($valueHistogramKeys, 0, 1);
            $highestCard = $this->getHighCard($notIncluding);
            $fourOfAKind[] = $highestCard; // Append highestCard to fourOfAKind
            $this->hand = $fourOfAKind;
            $this->rank = 8;
            $this->description = 'Four of a Kind, ' . $this->hand[0]->getValue() . 's';
        }
        elseif ($this->isFullHouse())
        {
            $this->hand = array_merge(array_slice(reset($this->valueHistogram), 0, 3), array_slice(next($this->valueHistogram), 0, 2));
            $this->rank = 7;
            $this->description = 'Full House, ' . $this->hand[0]->getValue() . 's full of ' . $this->hand[3]->getValue() . 's';
        }
        elseif ($this->isFlush())
        {
            $this->hand = array_slice(reset($this->suitHistogram), 0, 5);
            $this->rank = 6;
            $this->description = 'Flush, ' . $this->hand[0]->getValue() . ' high of ' . $this->hand[0]->getSuit();
        }
        elseif ($this->isStraight())
        {
            // Loop through the foundStraight and return the first Card at valueIndex
            $this->hand = array_map(fn($valueIndex) => $this->sortedValues[$valueIndex][0], $this->foundStraight);
            $this->rank = 5;
            $this->description = 'Straight, ' . $this->hand[0]->getValue() . ' to ' . $this->hand[4]->getValue();
        }
        elseif ($this->isThreeOfAKind())
        {
            $this->hand = array_merge(array_slice(reset($this->valueHistogram), 0, 3), array_slice(next($this->valueHistogram), 0, 1), array_slice(next($this->valueHistogram), 0, 1));
            $this->rank = 4;
            $this->description = 'Three of a Kind, ' . $this->hand[0]->getValue() .'s';
        }
        elseif ($this->isTwoPair())
        {
            $twoPair = array_merge(array_slice(reset($this->valueHistogram), 0, 2), array_slice(next($this->valueHistogram), 0, 2));
            $valueHistogramKeys = array_keys($this->valueHistogram);
            $notIncluding = array_splice($valueHistogramKeys, 0, 2);
            $highestCard = $this->getHighCard($notIncluding);
            $twoPair[] = $highestCard; // Append highestCard to twoPair
            $this->hand = $twoPair;
            $this->rank = 3;
            $this->description = 'Two Pair, ' . $this->hand[0]->getValue() .'s and ' . $this->hand[2]->getValue() .'s';
        }
        elseif ($this->isOnePair())
        {
            $this->hand = array_merge(
                                array_slice(reset($this->valueHistogram), 0, 2),
                                array_slice(next($this->valueHistogram), 0, 1),
                                array_slice(next($this->valueHistogram), 0, 1),
                                array_slice(next($this->valueHistogram), 0, 1)
                        );
            $this->rank = 2;
            $this->description = 'One Pair, ' . $this->hand[0]->getValue() .'s';
        }
        elseif ($this->isHighCard())
        {
            $this->hand = array_merge(
                                array_slice(reset($this->valueHistogram), 0, 1),
                                array_slice(next($this->valueHistogram), 0, 1),
                                array_slice(next($this->valueHistogram), 0, 1),
                                array_slice(next($this->valueHistogram), 0, 1),
                                array_slice(next($this->valueHistogram), 0, 1)
                        );
            $this->rank = 1;
            $this->description = 'High Card, ' . $this->hand[0]->getValue();
        }
        
        if (count($this->hand) !== 5) {
            throw new InvalidHandRankingException('Unable to determine best hand rank');
        }

        // Generate hand short description by combining the shortDescription of each card in the best hand
        foreach($this->hand as $card) {
            $this->shortDescription .= $card->getShortDescription();
        }
    }

    /**
    * Returns the next highest card rank excluding certains values
    * This is needed for Four of a Kind and Two Pair
    * valueHistogram for 7sJs9dJc7hJhJdAh would be J=4, 7=2, A=1, 9=1
    * Best hand is JJJJA and not JJJJ7 so we can't just get the second value of the histogram
    * Similary if three pairs are given but best hand is two pair
    * valueHistogram for 7sJs9dKc7hKhJdAh would be K=2, J=2, 7=2, A=1, 9=1
    * Best hand is KKJJA and not KKJJ7
    * 
    * @param array $notIncluding
    * @return Card
    */
    private function getHighCard(array $notIncluding): Card
    {
        $valueHistogramKeys = array_keys($this->valueHistogram);
        $otherKeys = array_diff($valueHistogramKeys, $notIncluding);
        $highestValue = max($otherKeys);

        if(! $highestValue) {
            throw new InvalidHandRankingException('No high card');
        }

        // Return the first card of the maximum value rank.
        return $this->valueHistogram[$highestValue][0];
    }

    /**
    * Returns if the hand ranking is a Royal flush
    * First check if a flush exists, and the top five flush cards equal a Royal flush
    * Cards in each suitHistogram are already sorted high to low
    * 
    * @return bool
    */
    public function isRoyalFlush(): Bool
    {
        if ($this->isFlush()) {
            $flushCardValues = array_map(fn($card) => (int) $card->getValueRank(), reset($this->suitHistogram));
            $fiveCards = array_slice($flushCardValues, 0, 5);
            return $fiveCards === [14, 13, 12, 11, 10];
        }

        return false;
    }

    /**
    * Returns if the hand ranking is a straight flush
    * First check if a flush exists
    * If so, send through card values of given flush to findStraight
    * Cards in each suitHistogram are already sorted high to low
    * 
    * @return bool
    */
    public function isStraightFlush(): Bool
    {
        if ($this->isFlush()) {
            $flushCardValues = array_map(fn($card) => (int) $card->getValueRank(), reset($this->suitHistogram));
            return $this->findStraight($flushCardValues);
        }

        return false;
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
        return (count(reset($this->valueHistogram)) === 4
                && count(next($this->valueHistogram)) >= 1);
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
        return (count(reset($this->valueHistogram)) === 3
                && count(next($this->valueHistogram)) >= 2);
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

        return $this->findStraight($sortedValues);
    }

    /**
    * Given a list of card values, find a straight
    * 
    * @param array $values
    * @return bool
    */
    private function findStraight($values): Bool
    {
        do {
            // Take first five cards index values
            $fiveCards = array_slice($values, 0, 5);
            // Build a potential straight based off the first value of the first card
            $compareStraight = range($values[0], $values[0]-4);
            // If fiveCards is the same as the compareStraight that's been built, then a straight has been found
            if ($fiveCards === $compareStraight) {
                $this->foundStraight = $fiveCards;
                return true;
            }
            // If not sequential then remove first element and repeat while there are at least five cards
            array_splice($values, 0, 1);
        } while(count($values) > 4);

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
        return (count(reset($this->valueHistogram)) === 3
                && count(next($this->valueHistogram)) === 1
                && count(next($this->valueHistogram)) === 1);
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
        return (count(reset($this->valueHistogram)) === 2
                && count(next($this->valueHistogram)) === 2
                && count(next($this->valueHistogram)) >= 1);
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
        return (count(reset($this->valueHistogram)) === 2
                && count(next($this->valueHistogram)) === 1
                && count(next($this->valueHistogram)) === 1
                && count(next($this->valueHistogram)) === 1);
    }

    /**
    * Returns if the hand ranking is one pairs
    * Determined by if the count of the each elemt is exactly one
    * 
    * @return bool
    */
    public function isHighCard(): Bool
    {
        return (count(reset($this->valueHistogram)) === 1
                && count(next($this->valueHistogram)) === 1
                && count(next($this->valueHistogram)) === 1
                && count(next($this->valueHistogram)) === 1
                && count(next($this->valueHistogram)) === 1);
    }
}