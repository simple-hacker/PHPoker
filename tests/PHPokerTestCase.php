<?php

namespace simplehacker\PHPoker\Tests;

use PHPUnit\Framework\TestCase;
use simplehacker\PHPoker\Player;

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
}