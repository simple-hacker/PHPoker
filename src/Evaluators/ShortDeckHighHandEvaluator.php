<?php

namespace simplehacker\PHPoker\Evaluators;

use simplehacker\PHPoker\Exceptions\InvalidHandException;
use simplehacker\PHPoker\Exceptions\InvalidShortDeckHandException;

class ShortDeckHighHandEvaluator extends HighHandEvaluator
{
    /**
     * The hand type rank values
     * In short deck, a Flush beats a Full House
     */
    const FLUSH_RANK            = 7;
    const FULL_HOUSE_RANK       = 6;

    /**
     * The index for a low Ace
     * 
     * @var integer
     */
    protected $lowAceValue = 5;

    /**
    * Validate the Cards given and generates a histogram of card values and suits
    * This constructor then checks to see if any low cards have been passed through
    * 
    * @param array|string $cards
    */
    public function __construct($cards)
    {
        $this->validateCards($cards);

        foreach($this->cards as $card) {
            if ($card->getValueRank() < 6) {
                throw new InvalidShortDeckHandException('Invalid short deck Card ' . $card->getShortDescription());
            }
        }

        $this->valueHistogram = $this->generateValueHistogram();
        $this->sortedValues = $this->sortValueHistogramAccordingToValue();

        $this->suitHistogram = $this->generateSuitHistogram();

        $this->generateHand();
        $this->computeHandValues();
    }

    /**
    * Gets the best five cards of given cards
    * Assigns a hand rank value to compare with other hands
    * Updates hand descriptions
    * Same as HighHandEvaluator except check for Flush before Full House
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
        elseif ($this->isFlush())
        {
            $this->flush();
        }
        elseif ($this->isFullHouse())
        {
            $this->fullHouse();
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
    }
}