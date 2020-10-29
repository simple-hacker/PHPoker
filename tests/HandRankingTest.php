<?php

namespace simplehacker\PHPoker\Tests;

use simplehacker\PHPoker\Card;

use PHPUnit\Framework\TestCase;
use simplehacker\PHPoker\Exceptions\InvalidHandRankingException;
use simplehacker\PHPoker\HandRanking;

class HandRankingTest extends TestCase
{
    /** @test */
    public function an_array_of_cards_can_be_given_when_instantiating()
    {
        $cards = [new Card('Ac'), '2d', new Card('3s'), new Card('4d'), new Card('5s')];
        $hand = new HandRanking($cards);

        $this->assertCount(5, $hand->getCards());
    }

    /** @test */
    public function a_string_of_cards_can_be_given_when_instantiating()
    {
        $cards = 'Ac2h3s4d5s';
        $hand = new HandRanking($cards);

        $this->assertCount(5, $hand->getCards());
    }

    /** @test */
    public function at_least_five_cards_need_to_be_provided()
    {
        $this->expectException(InvalidHandRankingException::class);

        // Only four cards provided
        $cards = 'Ac2h3s4d';
        $hand = new HandRanking($cards);
    }

    /** @test */
    public function duplicate_cards_are_not_valid()
    {
        $this->expectException(InvalidHandRankingException::class);
        
        $cards = 'Ac2h3s4d3s';
        $hand = new HandRanking($cards);
    }
}