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

    /** @test */
    public function cards_are_grouped_and_sorted_by_suit()
    {
        // 3 hearts, 1 diamond and 1 spade.
        $AceH = new Card('Ah');
        $TwoH = new Card('2h');
        $ThreeH = new Card('3h');
        $FourD = new Card('4d');
        $FiveS = new Card('5s');

        $hand = new HandRanking([$FourD, $FiveS, $ThreeH, $AceH, $TwoH]);

        $expected = [
            3 => [$ThreeH, $TwoH, $AceH],
            4 => [$FiveS],
            2 => [$FourD]
        ];

        // Note if this fails in the future it could be to do with Ace value rank being 1 instead of 14
        $this->assertEquals($expected, $hand->getSuitHistogram());
    }

    /**
     * @test
     * @dataProvider flushes
    */
    public function hand_ranking_is_a_flush($hand, $isFlush)
    {
        $hand = new HandRanking($hand);

        $this->assertEquals($hand->isFlush(), $isFlush);
    }

    public function flushes() {
        return [
            ['7h3s8h2hKh4dTh', true],
            ['3s4s5s6sKs', true],
            ['2d3s4s8c9c', false],
        ];
    } 
}