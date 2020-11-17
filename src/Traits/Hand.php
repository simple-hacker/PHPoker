<?php

namespace simplehacker\PHPoker\Traits;

trait Hand
{
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
    * Returns the protected array of best hand cards 
    *
    * @return array
    */
    public function getHand(): Array
    {
        return $this->hand;
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
    * Returns the hand value without kickers
    *
    * @return integer
    */
    public function getHandValueWithoutKickers(): Int
    {
        return $this->handValueWithoutKickers;
    }
}