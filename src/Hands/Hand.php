<?php

namespace simplehacker\PHPoker\Hands;

use simplehacker\PHPoker\Card;
use simplehacker\PHPoker\Traits\Hand as HandTrait;
use simplehacker\PHPoker\Evaluators\HighHandEvaluator;

abstract class Hand
{
    use HandTrait;
    
    /**
    * If the hand was determined by kickers, when comparing to another hand, then set to true
    * Used when generating a description to include kicker information
    * 
    * @var bool
    */
    public $determinedByKickers = false;

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
    * Returns the five cards of the HandEvaluator
    * 
    * @param 
    * @return array
    */
    public function getHand(): Array
    {
        return $this->hand;
    }

    /**
    * Returns the hand rank of the HandEvaluator
    *
    * @return integer
    */
    public function getHandRank(): Int
    {
        return $this->handRank;
    }

    /**
    * Returns the hand value of the HandEvaluator
    *
    * @return integer
    */
    public function getHandValue(): Int
    {
        return $this->handValue;
    }

    /**
    * Returns the hand value without kickers of the HandEvaluator
    *
    * @return integer
    */
    public function getHandValueWithoutKickers(): Int
    {
        return $this->handValueWithoutKickers;
    }

    /**
    * Returns short values description of the best hand found
    * e.g. Kh9c6h4s3d
    *
    * @return string
    */
    public function getShortDescription(): String
    {
        $shortDescription = '';

        foreach($this->getHand() as $card) {
            $shortDescription .= $card->getShortDescription();
        }

        return $shortDescription;
    }

    /**
    * Returns the description of the best hand found
    * with kickerDescription if it exists
    * e.g. Four of a Kind, Jacks
    *
    * @return string
    */
    public function getDescription(): String
    {
        if (! $this->description) {
            $this->setDescription();
        }

        return ($this->determinedByKickers) ? $this->description . $this->getKickerDescription() : $this->description;
    }

    /**
    * Set the description of the hand based on the hand rank
    * 
    * @return void
    */
    public function setDescription(): Void
    {
        switch($this->handRank) {
            case HighHandEvaluator::HIGH_CARD_RANK:
                $this->description = 'High Card, ' . $this->hand[0]->getValue();
                break;
            case HighHandEvaluator::ONE_PAIR_RANK:
                $this->description = 'One Pair, ' . $this->hand[0]->getValue() .'s';
                break;
            case HighHandEvaluator::TWO_PAIR_RANK:
                $this->description = 'Two Pair, ' . $this->hand[0]->getValue() .'s and ' . $this->hand[2]->getValue() .'s';
                break;
            case HighHandEvaluator::THREE_OF_A_KIND_RANK:
                $this->description = 'Three of a Kind, ' . $this->hand[0]->getValue() .'s';
                break;
            case HighHandEvaluator::STRAIGHT_RANK:
                $this->description = 'Straight, ' . $this->hand[0]->getValue() . ' to ' . $this->hand[4]->getValue();
                break;
            case HighHandEvaluator::FLUSH_RANK:
                $this->description = 'Flush, ' . $this->hand[0]->getValue() . ' high of ' . $this->hand[0]->getSuit();
                break;
            case HighHandEvaluator::FULL_HOUSE_RANK:
                $this->description = 'Full House, ' . $this->hand[0]->getValue() . 's full of ' . $this->hand[3]->getValue() . 's';
                break;
            case HighHandEvaluator::FOUR_OF_A_KIND_RANK:
                $this->description = 'Four of a Kind, ' . $this->hand[0]->getValue() . 's';
                break;
            case HighHandEvaluator::STRAIGHT_FLUSH_RANK:
                $this->description = 'Straight Flush, ' . $this->hand[0]->getValue() . ' to ' . $this->hand[4]->getValue() . ' of ' . $this->hand[0]->getSuit();
                break;
            case HighHandEvaluator::ROYAL_FLUSH_RANK:
                $this->description = 'Royal Flush, ' . $this->hand[0]->getValue() . ' to ' . $this->hand[4]->getValue() . ' of ' . $this->hand[0]->getSuit();
                break;
        }
    }

    /**
    * If the hand was determined by kickers when comparing hands
    * Generate the kickers description
    *
    * @return string
    */
    public function getKickerDescription(): String
    {
        $numberOfSignificantCards = HighHandEvaluator::numberOfSignificantCards($this->handRank);

        // Remove the most significant cards of the hand (by offsetting array_slice), leaving only the kickers
        $kickers = array_slice($this->getHand(), $numberOfSignificantCards);
        
        // Map over and return the card value description from each card
        $kickers = array_map(fn($kicker) => $kicker->getValue(), $kickers);
        $kickerDescriptionEnd = (count($kickers) > 1) ? ' kickers' : ' kicker';
        
        // If we have kickers then glue kickers together and generate description
        // Else return empty string        
        return ($kickers) ? ", with " . implode("+", $kickers). $kickerDescriptionEnd : '';
    }
}