<?php

namespace simplehacker\PHPoker\Tests;

use simplehacker\PHPoker\Card;
use simplehacker\PHPoker\Hand;
use simplehacker\PHPoker\Tests\PHPokerTestCase;
use simplehacker\PHPoker\Exceptions\InvalidHandException;
use simplehacker\PHPoker\HighHandEvaluator;

class HandTest extends PHPokerTestCase
{
    /** @test */
    public function an_array_of_cards_can_be_given_when_instantiating()
    {
        $cards = [new Card('Ac'), '2d', new Card('3s'), new Card('4d'), new Card('5s')];
        $hand = new HighHandEvaluator($cards);

        $this->assertCount(5, $hand->getCards());
    }

    /** @test */
    public function a_string_of_cards_can_be_given_when_instantiating()
    {
        $cards = 'Ac2h3s4d5s';
        $hand = new HighHandEvaluator($cards);

        $this->assertCount(5, $hand->getCards());
    }

    /** @test */
    public function at_least_five_cards_need_to_be_provided()
    {
        $this->expectException(InvalidHandException::class);

        // Only four cards provided
        $cards = 'Ac2h3s4d';
        $hand = new HighHandEvaluator($cards);
    }

    /** @test */
    public function duplicate_cards_are_not_valid()
    {
        $this->expectException(InvalidHandException::class);
        
        $cards = 'Ac2h3s4d3s';
        $hand = new HighHandEvaluator($cards);
    }
}