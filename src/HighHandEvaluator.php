<?php

namespace simplehacker\PHPoker;

use simplehacker\PHPoker\Hand;
use simplehacker\PHPoker\Exceptions\InvalidHandException;

class HighHandEvaluator extends Hand
{
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
    * The hand type rank values
    */
    const ROYAL_FLUSH_RANK      = 10;
    const STRAIGHT_FLUSH_RANK   = 9;
    const FOUR_OF_A_KIND_RANK   = 8;
    const FULL_HOUSE_RANK       = 7;
    const FLUSH_RANK            = 6;
    const STRAIGHT_RANK         = 5;
    const THREE_OF_A_KIND_RANK  = 4;
    const TWO_PAIR_RANK         = 3;
    const ONE_PAIR_RANK         = 2;
    const HIGH_CARD_RANK        = 1;

    /**
    * Validate the Cards given and generates a histogram of card values and suits
    * This constructor then generates a suit histogram needed for high hands
    * and generates the best high hand
    * 
    * @param array|string $cards
    */
    public function __construct($cards)
    {
        $this->validateCards($cards);

        $this->valueHistogram = $this->generateValueHistogram();
        $this->sortedValues = $this->sortValueHistogramAccordingToValue();

        $this->suitHistogram = $this->generateSuitHistogram();

        $this->generateHand();
        $this->computeHandValues();
    }

    /**
    * Returns the description of the best hand found
    * with kickerDescription if it exists
    * e.g. Four of a Kind, Jacks
    *
    * @return string
    */
    public function getDescription(): string
    {
        return ($this->determinedByKickers) ? $this->description . $this->getKickerDescription() : $this->description;
    }

    /**
    * If the hand was determined by kickers when comparing hands
    * Generate the kickers description
    *
    * @return string
    */
    public function getKickerDescription(): string
    {
        $numberOfSignificantCards = $this->numberOfSignificantCards();

        // Remove the most significant cards of the hand (by offsetting array_slice), leaving only the kickers
        $kickers = array_slice($this->hand, $numberOfSignificantCards);
        
        // Map over and return the card value description from each card
        $kickers = array_map(fn($kicker) => $kicker->getValue(), $kickers);
        $kickerDescriptionEnd = (count($kickers) > 1) ? ' kickers' : ' kicker';
        
        // If we have kickers then glue kickers together and generate description
        // Else return empty string        
        return ($kickers) ? ", with " . implode("+", $kickers). $kickerDescriptionEnd : '';
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
    protected function generateSuitHistogram(): Array
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
    protected function generateHand()
    {
        if ($this->isRoyalFlush())
        {
            $this->royalFlush();
        }
        elseif ($this->isStraightFlush())
        {
            $this->straightFlush();
        }
        elseif ($this->isFourOfAKind())
        {
            $this->fourOfAKind();
        }
        elseif ($this->isFullHouse())
        {
            $this->fullHouse();
        }
        elseif ($this->isFlush())
        {
            $this->flush();
        }
        elseif ($this->isStraight())
        {
            $this->straight();
        }
        elseif ($this->isThreeOfAKind())
        {
            $this->threeOfAKind();
        }
        elseif ($this->isTwoPair())
        {
            $this->twoPair();
        }
        elseif ($this->isOnePair())
        {
            $this->onePair();
        }
        elseif ($this->isHighCard())
        {
            $this->highCard();
        }
        
        if (count($this->hand) !== 5) {
            throw new InvalidHandException('Unable to determine best hand rank');
        }

        // Generate hand short description by combining the shortDescription of each card in the best hand
        foreach($this->hand as $card) {
            $this->shortDescription .= $card->getShortDescription();
        }
    }

    /**
    * Returns the next highest card rank excluding certains values
    * This is needed for Four of a Kind and Two Pair
    * valueHistogram for 7sJsJc7hJhJdAh would be J=4, 7=2, A=1
    * Best hand is JJJJA and not JJJJ7 so we can't just get the second value of the histogram
    * Similary if three pairs are given but best hand is two pair
    * valueHistogram for 7sJsKc7hKhJdAh would be K=2, J=2, 7=2, A=1
    * Best hand is KKJJA and not KKJJ7
    * 
    * @param array $valueHistogramKeys
    * @param array $notIncluding
    * @return Card
    */
    protected function getHighCard(int $removeTopN = 0): Card
    {
        if(! ($removeTopN == 1 || $removeTopN == 2)) {
            throw new InvalidHandException('Removing too many cards to determine best high card');
        }

        $valueHistogramKeys = array_keys($this->valueHistogram);
        // Remove the first N elements
        $otherKeys = array_splice($valueHistogramKeys, $removeTopN);
        $highestValue = max($otherKeys);

        if(! $highestValue) {
            throw new InvalidHandException('No high card');
        }

        // Return the first card of the maximum value rank.
        return $this->valueHistogram[$highestValue][0];
    }

    /**
    * Generate a numerical value for hand ranking
    * Used for quicker comparing of hands
    * 
    * @return void
    */
    protected function computeHandValues(): void
    {
        // The hand is sorted with the most significant cards to the front i.e. [14, 12, 10, 6, 5]
        // https://stackoverflow.com/a/42396124/7095440
        // Convert hand to a 24 bit binary
        // [HAND_RANK Binary] [Card 0 Binary] [Card 1 Binary] [Card 2 Binary] [Card 3 Binary] [Card 4 Binary]

        // For certain hand types, only the first n cards are significant
        // Used when winning hands of the same type were eventually determined by their kicker
        // so that an appropriate description can be generated
        // e.g. AAKK8 vs AAKK4
        // They both have the same significant card value (AAKK)
        // Take away one to match card indexes
        $numberOfSignificantCards = $this->numberOfSignificantCards() - 1;

        // Hand rank binary padded to four bits
        $handValueBinary = $handValueWithoutKickersBinary = sprintf("%04d", decbin($this->handRank));

        // Append card value binary padded to four bits, to binary string
        // The hand has already been normalised to left most significant cards
        foreach($this->hand as $index => $card) {
            // Add card binary to actual hand value
            $handValueBinary .= sprintf("%04d", decbin($card->getValueRank()));
            // If Card is significant for Hand_TYPE then add Card value binary
            // Else it's a kicker, append 0000 instead
            $handValueWithoutKickersBinary .= ($index <= $numberOfSignificantCards) ? sprintf("%04d", decbin($card->getValueRank())) : '0000';
        }

        // Convert 24 bit binary to an integer
        $this->handValue = bindec($handValueBinary);
        $this->handValueWithoutKickers = bindec($handValueWithoutKickersBinary);
    }

    /**
    * Returns the number of significant cards in a certain hand type
    * e.g. With three of a kind, the first three cards
    * e.g. With two pair, the first four cards
    * e.g. With a straight, all cards
    * 
    * @return int
    */
    private function numberOfSignificantCards(): Int
    {
        // These are the types of hands that could have kickers
        switch($this->handRank) {
            case static::HIGH_CARD_RANK:
                return 1;
            case static::ONE_PAIR_RANK:
                return 2;
            case static::TWO_PAIR_RANK:
                return 4;
            case static::THREE_OF_A_KIND_RANK:
                return 3;
            case static::FLUSH_RANK:
                return 1;
            case static::FOUR_OF_A_KIND_RANK:
                return 4;
            default:
                // Straights, Full Houses and Straight Flushes, all cards are significant
                return 5;
        }
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
    * Determined by if the count of the each element is exactly one
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

    /**
    * Set Hand properties to be the Royal Flush
    * 
    * @return void
    */
    protected function royalFlush(): void
    {
        $this->hand = array_slice(reset($this->suitHistogram), 0, 5);
        $this->handRank = static::ROYAL_FLUSH_RANK;
        $this->description = 'Royal Flush, ' . $this->hand[0]->getValue() . ' to ' . $this->hand[4]->getValue() . ' of ' . $this->hand[0]->getSuit();
    }

    /**
    * Set Hand properties to be the Straight Flush
    * 
    * @return void
    */
    protected function straightFlush(): void
    {
        $this->hand = array_slice(reset($this->suitHistogram), 0, 5);
        $this->handRank = static::STRAIGHT_FLUSH_RANK;
        $this->description = 'Straight Flush, ' . $this->hand[0]->getValue() . ' to ' . $this->hand[4]->getValue() . ' of ' . $this->hand[0]->getSuit();
    }

    /**
    * Set Hand properties to be the Four of a Kind
    * 
    * @return void
    */
    protected function fourOfAKind(): void
    {
        $fourOfAKind = array_slice(reset($this->valueHistogram), 0, 4);
        // Get the best card after removing top 1 of histogram (four of a kind is the top of value histogram)
        $withoutTopN = 1;
        $highestCard = $this->getHighCard($withoutTopN);
        $fourOfAKind[] = $highestCard; // Append highestCard to fourOfAKind
        $this->hand = $fourOfAKind;
        $this->handRank = static::FOUR_OF_A_KIND_RANK;
        $this->description = 'Four of a Kind, ' . $this->hand[0]->getValue() . 's';
    }

    /**
    * Set Hand properties to be the Full House
    * 
    * @return void
    */
    protected function fullHouse(): void
    {
        $this->hand = array_merge(array_slice(reset($this->valueHistogram), 0, 3), array_slice(next($this->valueHistogram), 0, 2));
        $this->handRank = static::FULL_HOUSE_RANK;
        $this->description = 'Full House, ' . $this->hand[0]->getValue() . 's full of ' . $this->hand[3]->getValue() . 's';
    }

    /**
    * Set Hand properties to be the Flush
    * 
    * @return void
    */
    protected function flush(): void
    {
        $this->hand = array_slice(reset($this->suitHistogram), 0, 5);
        $this->handRank = static::FLUSH_RANK;
        $this->description = 'Flush, ' . $this->hand[0]->getValue() . ' high of ' . $this->hand[0]->getSuit();
    }

    /**
    * Set Hand properties to be the Straight
    * 
    * @return void
    */
    protected function straight(): void
    {
        // Loop through the foundStraight and return the first Card at valueIndex
        $this->hand = array_map(fn($valueIndex) => $this->sortedValues[$valueIndex][0], $this->foundStraight);
        $this->handRank = static::STRAIGHT_RANK;
        $this->description = 'Straight, ' . $this->hand[0]->getValue() . ' to ' . $this->hand[4]->getValue();
    }

    /**
    * Set Hand properties to be the Three of a Kind
    * 
    * @return void
    */
    protected function threeOfAKind(): void
    {
        $this->hand = array_merge(array_slice(reset($this->valueHistogram), 0, 3), array_slice(next($this->valueHistogram), 0, 1), array_slice(next($this->valueHistogram), 0, 1));
        $this->handRank = static::THREE_OF_A_KIND_RANK;
        $this->description = 'Three of a Kind, ' . $this->hand[0]->getValue() .'s';
    }

    /**
    * Set Hand properties to be the Two Pair
    * 
    * @return void
    */
    protected function twoPair(): void
    {
        $twoPair = array_merge(array_slice(reset($this->valueHistogram), 0, 2), array_slice(next($this->valueHistogram), 0, 2));
        // Get the best card after removing top 2 of histogram (Two pair is the top two of value histogram)
        $withoutTopN = 2;
        $highestCard = $this->getHighCard($withoutTopN);
        $twoPair[] = $highestCard; // Append highestCard to twoPair
        $this->hand = $twoPair;
        $this->handRank = static::TWO_PAIR_RANK;
        $this->description = 'Two Pair, ' . $this->hand[0]->getValue() .'s and ' . $this->hand[2]->getValue() .'s';
    }

    /**
    * Set Hand properties to be the One Pair
    * 
    * @return void
    */
    protected function onePair(): void
    {
        $this->hand = array_merge(
            array_slice(reset($this->valueHistogram), 0, 2),
            array_slice(next($this->valueHistogram), 0, 1),
            array_slice(next($this->valueHistogram), 0, 1),
            array_slice(next($this->valueHistogram), 0, 1)
            );
        $this->handRank = static::ONE_PAIR_RANK;
        $this->description = 'One Pair, ' . $this->hand[0]->getValue() .'s';
    }

    /**
    * Set Hand properties to be the High Card
    * 
    * @return void
    */
    protected function highCard(): void
    {
        $this->hand = array_merge(
            array_slice(reset($this->valueHistogram), 0, 1),
            array_slice(next($this->valueHistogram), 0, 1),
            array_slice(next($this->valueHistogram), 0, 1),
            array_slice(next($this->valueHistogram), 0, 1),
            array_slice(next($this->valueHistogram), 0, 1)
            );
        $this->handRank = static::HIGH_CARD_RANK;
        $this->description = 'High Card, ' . $this->hand[0]->getValue();
    }
}