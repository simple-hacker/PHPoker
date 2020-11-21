<?php

namespace simplehacker\PHPoker\Hands;

use simplehacker\PHPoker\Card;
use simplehacker\PHPoker\Evaluators\HighHandEvaluator;
use simplehacker\PHPoker\Exceptions\InvalidHandException;

class OmahaHand extends Hand
{
    /**
    * Sets the Hand properties to be the values generated by HighHandEvaluator
    * 
    */
    public function __construct($communityCards, $holeCards)
    {
        $communityCards = Card::convertToCards($communityCards);
        $holeCards = Card::convertToCards($holeCards);

        if (count($communityCards) !== 5) {
            throw new InvalidHandException('Omaha requires five community cards');
        }

        if (count($holeCards) !== 4) {
            throw new InvalidHandException('Omaha requires two hole cards cards');
        }

        $threeCommunityCardsCombinations = $this->combinations_set($communityCards, 3);
        $twoHoleCardsCombinations = $this->combinations_set($holeCards, 2);

        foreach ($threeCommunityCardsCombinations as $threeCommunityCards)
        {
            foreach ($twoHoleCardsCombinations as $twoCommunityCards)
            {
                // Evaluate the three communityCard and two holeCards with the HighHandEvaluator
                [$hand, $handRank, $handValue, $handValueWithoutKickers] = (new HighHandEvaluator([...$threeCommunityCards, ...$twoCommunityCards]))->evaluate();

                // If the latest handValue is greater than the current best handValue then overwrite
                // all hand properties                
                if ($handValue > $this->handValue){
                    [$this->hand, $this->handRank, $this->handValue, $this->handValueWithoutKickers] = [$hand, $handRank, $handValue, $handValueWithoutKickers];
                }
            }
        }
    }

    private function combinations_set($set = [], $size = 0) {
        if ($size == 0) {
            return [[]];
        }
     
        if ($set == []) {
            return [];
        }
     
     
        $prefix = [array_shift($set)];
     
        $result = [];
     
        foreach ($this->combinations_set($set, $size-1) as $suffix) {
            $result[] = array_merge($prefix, $suffix);
        }
     
        foreach ($this->combinations_set($set, $size) as $next) {
            $result[] = $next;
        }
     
        return $result;
    }
}