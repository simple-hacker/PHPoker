<?php

namespace simplehacker\PHPoker\Evaluators;

class LowHandEvaluator extends HandEvaluator
{
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

        $this->generateHand();
        $this->computeHandValues();
    }

    /**
    * Returns the description of the best low hand
    *
    * @return string
    */
    public function getDescription(): string
    {
        return ($this->hand) ? $this->description : 'No Low Hand';
    }

    /**
    * Returns if the Cards given contain a valid low hand
    * If the Card sorted value histogram contains at least five different cards
    * Take the bottom five cards as it's sorted in order
    * If the highest of the bottom five cards is an eight or lower, then hand has a low
    * 
    * @return bool
    */
    public function hasLow(): Bool
    {
        if (count($this->sortedValues) < 5) {
            return false;
        }
        
        $lowestCards = array_slice($this->sortedValues, -5, 5, true);

        return (array_key_first($lowestCards) <= 8);
    }

    /**
    * Gets the lowest five cards if a low exists
    * 
    * @return void
    */
    protected function generateHand()
    {
        if ($this->hasLow()) {
            $lowestCards = array_slice($this->sortedValues, -5, 5, true);
            $lowestCardsKeys = array_keys($lowestCards);

            // Get the first card of each histogram bucket
            $this->hand = array_map(fn($cardValue) => $this->sortedValues[$cardValue][0], $lowestCardsKeys);
            $this->description = $this->hand[0]->getValue() . ' low';
        }
    }

    /**
    * Generate a numerical value for low hand
    * Used for quicker comparing of hands
    * 
    * @return void
    */
    protected function computeHandValues(): void
    {
        $handValueBinary = '';

        // Append card value binary padded to four bits, to binary string
        // The hand has already been normalised to left most significant cards
        foreach($this->hand as $index => $card) {
            // Add card binary to actual hand value
            $handValueBinary .= sprintf("%04d", decbin($card->getValueRank())) . sprintf("%04d", decbin($card->getSuitRank()));
        }

        // Convert 24 bit binary to an integer
        $this->handValue = bindec($handValueBinary);
    }
}