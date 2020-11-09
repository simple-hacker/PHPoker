<?php

namespace simplehacker\PHPoker\Tests;

use Exception;
use simplehacker\PHPoker\Card;
use PHPUnit\Framework\TestCase;
use simplehacker\PHPoker\Player;
use simplehacker\PHPoker\Games\NoLimitHoldem;

abstract class PHPokerTestCase extends TestCase
{
    /**
    * Generates n Player instances
    * 
    * @param int $n
    * @return array
    */
    public function createPlayers(int $n = 1): Array
    {
        if ($n < 1) $n = 1;

        $players = array_map(fn() => new Player(), array_fill(0, $n, null));
        return $players;
    }

    /**
    * Create a NoLimitHoldemHand with specified cards for testing purposes
    * 
    * @param array $communityCards
    * @param array $playersWithHoleCards
    * @return NoLimitHoldem
    */
    public function createNoLimitHoldemHand(array $communityCards, array $playersWithHoleCards): NoLimitHoldem
    {
        $communityCards = array_map(fn($card) => new Card($card), $communityCards);

        $allCards = $communityCards;
        
        $players = [];

        foreach ($playersWithHoleCards as $holeCards) {
            $player = new Player();
            $holeCards = array_map(fn($card) => new Card($card), $holeCards);
            $allCards = [...$allCards, ...$holeCards];
            $player->giveCards($holeCards);
            $players[] = $player;
        }
        
        $uniqueAllCards = array_unique($allCards);
        if (count($uniqueAllCards) != count($allCards)) {
            throw new Exception('Duplicate cards given when testing');
            exit();
        }

        $handMock = new NoLimitHoldem($players);
        
        $reflectionClass = new \ReflectionClass($handMock);

        $property = $reflectionClass->getProperty('communityCards');
        $property->setAccessible(true);
        $property->setValue($handMock, $communityCards);
        $property->setAccessible(false);

        return $handMock;
    }

    /**
    * Create an instance of Player with hole cards.
    * 
    * @param array $holeCards
    * @return Player
    */
    public function createPlayerWithCards($holeCards): Player
    {
        $player = new Player();
        $holeCards = array_map(fn($card) => new Card($card), $holeCards);
        $player->giveCards($holeCards);
        return $player;
    }
}