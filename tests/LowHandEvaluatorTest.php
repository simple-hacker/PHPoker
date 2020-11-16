<?php

namespace simplehacker\PHPoker\Tests;

use simplehacker\PHPoker\Card;
use simplehacker\PHPoker\LowHandEvaluator;
use simplehacker\PHPoker\Exceptions\InvalidHandException;

class LowHandEvaluatorTest extends PHPokerTestCase
{
    /** @test */
    public function an_array_of_cards_can_be_given_when_instantiating()
    {
        $cards = [new Card('Ac'), '2d', new Card('3s'), new Card('4d'), new Card('5s')];
        $hand = new LowHandEvaluator($cards);

        $this->assertCount(5, $hand->getCards());
    }

    /** @test */
    public function at_least_five_cards_need_to_be_provided()
    {
        $this->expectException(InvalidHandException::class);

        // Only four cards provided
        $cards = 'Ac2h3s4d';
        $hand = new LowHandEvaluator($cards);
    }

    /**
    * @test
    * @dataProvider hasLowHands
    */
    public function hands_have_a_low_hand($hand, $isLow)
    {
        $hand = new LowHandEvaluator($hand);

        $this->assertEquals($hand->hasLow(), $isLow);
    }

    public function hasLowHands()
    {
        return [
            ['AcQcTcKcJc', false],
            ['AcQcTcKcJc9c8c', false],
            ['7h3h6h5h4h', true], // Straights and flushes don't count so this has a low
            ['8s8d5h3c2s', false], // Need at least five different cards lower
            ['Ah6s4c2d3s', true], // Low ace is okay
            ['9c7d6c5c4c', false], // Nine high is not a low
            ['4c7c8c2c4d3d3h3s', true], //87432
        ];
    }

    /**
    * @test
    * @dataProvider lowHands
    */
    public function generates_best_low_hand($cards, $expectedHand, $description)
    {
        $hand = new LowHandEvaluator($cards);

        $this->assertEquals($expectedHand, $hand->getHand());
        $this->assertEquals($description, $hand->getDescription());
    }

    public function lowHands() {
        return [
            ['AcQcTcKcJc', [], 'No Low Hand'],
            ['AcQcTcKcJc9c8c', [], 'No Low Hand'],
            ['7h3h6h5h4h', [new Card('7h'), new Card('6h'), new Card('5h'), new Card('4h'), new Card('3h')], 'Seven low'],
            ['8s8d5h3c2s', [], 'No Low Hand'],
            ['Ah6s4c2d3s', [new Card('6s'), new Card('4c'), new Card('3s'), new Card('2d'), new Card('Ah')], 'Six low'],
            ['9c7d6c5c4c', [], 'No Low Hand'],
            ['4c7c8c2c4d3d3h3s', [new Card('8c'), new Card('7c'), new Card('4d'), new Card('3s'), new Card('2c')], 'Eight low'],
        ];
    }

    /** @test */
    public function six_high_straight_value_is_greater_than_five_high_straight_value()
    {
        // Because we copy the Ace but set the straight index value to 1, when we compute
        // the binary we use the Ace value of 14 instead
        // Double checking 65432 is still greater than 54321

        $sixLow = new LowHandEvaluator('6h5d4c3s2s');
        $fiveLow = new LowHandEvaluator('5d4c3s2sAd');

        // Assert both straight
        $this->assertSame($sixLow->getHandRank(), $fiveLow->getHandRank());

        // Assert six high straight is bigger than five high straight
        $this->assertTrue($sixLow->getHandValue() > $fiveLow->getHandValue());
    }
}