<?php

namespace simplehacker\PHPoker\Tests;

use simplehacker\PHPoker\Card;

use simplehacker\PHPoker\Deck;
use PHPUnit\Framework\TestCase;

class DeckTest extends TestCase
{
    /** @test */
    public function deck_holds_52_cards()
    {
        $deck = new Deck();
        
        $this->assertCount(52, $deck->cards);
    }
}