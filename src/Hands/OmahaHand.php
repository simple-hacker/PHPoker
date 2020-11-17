<?php

namespace simplehacker\PHPoker\Hands;

use simplehacker\PHPoker\Card;
use simplehacker\PHPoker\Evaluators\HighHandEvaluator;

class OmahaHand extends Hand
{
    /**
    * Sets the Hand properties to be the values generated by HighHandEvaluator
    * 
    */
    public function __construct($communityCards, $holeCards)
    {
        // $communityCards = Card::convertToCards($communityCards);
        // $holeCards = Card::convertToCards($holeCards);

        // [$this->hand, $this->handRank, $this->handValue, $this->handValueWithoutKickers] = (new HighHandEvaluator([...$communityCards, ...$holeCards]))->evaluate();
    }
}