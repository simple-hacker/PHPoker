<?php

namespace simplehacker\PHPoker\Tests;

use simplehacker\PHPoker\Card;

use simplehacker\PHPoker\Deck;
use PHPUnit\Framework\TestCase;

class DeckTest extends TestCase
{
    /** @test */
    public function deck_can_be_instantiated()
    {
        $deck = new Deck();

        var_dump(Card::$suits);
        die();

        $this->assertTrue(true);
    }
}