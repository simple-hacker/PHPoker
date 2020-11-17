<?php

namespace simplehacker\PHPoker\Tests;

use simplehacker\PHPoker\Card;
use simplehacker\PHPoker\Tests\PHPokerTestCase;
use simplehacker\PHPoker\Evaluators\ShortDeckHighHandEvaluator;
use simplehacker\PHPoker\Exceptions\InvalidShortDeckHandException;

class ShortDeckHighHandEvaluatorTest extends PHPokerTestCase
{
    /**
    * @test
    * @dataProvider lowCards
    * */
    public function short_deck_hand_cannot_have_a_card_less_than_six($cards)
    {
        $this->expectException(InvalidShortDeckHandException::class);

        $hand = new ShortDeckHighHandEvaluator($cards);
    }

    public function lowCards()
    {
        return [
            ['AhKsJc9d8s5c'],
            ['AhKsJc9d8s4c'],
            ['AhKsJc9d8s3c'],
            ['AhKsJc9d8s2c'],
        ];
    }

    /**
    * @test
    * @dataProvider straights
    */
    public function hand_ranking_is_a_straight($hand, $isStraight)
    {
        $hand = new ShortDeckHighHandEvaluator($hand);

        $this->assertEquals($hand->isStraight(), $isStraight);
    }

    public function straights()
    {
        return [
            ['AhTsQcJcKd', true], // AKQJT Broadway straight, A high
            ['8h6cTsAd9c', false], // Not a straight
            ['8h6c7sAd9c', true], // A6789 is a low straight in short deck
        ];
    }

    /** @test */
    public function confirm_flush_beats_a_full_house()
    {
        // This hand is both a flush and a full house
        $hand = new ShortDeckHighHandEvaluator('KhKdKcQhQdTh8h6h');

        $this->assertEquals('KhQhTh8h6h', $hand->getShortDescription());
        $this->assertEquals(ShortDeckHighHandEvaluator::FLUSH_RANK, $hand->getHandRank());
    }

    /**
    * @test
    * @dataProvider bestHands
    */
    public function hand_ranking_gives_best_five_cards($cards, $hand, $handShortDescription, $handDescription, $handRank)
    {
        $handRanking = new ShortDeckHighHandEvaluator($cards);
        $this->assertEquals($handRanking->getHand(), $hand);
        $this->assertEquals($handRanking->getShortDescription(), $handShortDescription);
        $this->assertEquals($handRanking->getDescription(), $handDescription);
        $this->assertEquals($handRanking->getHandRank(), $handRank);
    }

    public function bestHands()
    {
        // High Card King
        // One Pair, Jacks
        // Two Pair, Kings and Fours
        // Three of a Kind, Jacks
        // Straight, King to Nine
        // Flush, King high Diamonds
        // Full House, Eights full of Threes
        // Four of a Kind, Jacks
        // Straight Flush, King to Nine of Diamonds
        // Royal Flush, Ace to Ten of Diamonds

        return [
            ['6hJdQdAdKdTd6s', [new Card('Ad'), new Card('Kd'), new Card('Qd'), new Card('Jd'), new Card('Td')], 'AdKdQdJdTd', 'Royal Flush, Ace to Ten of Diamonds', ShortDeckHighHandEvaluator::ROYAL_FLUSH_RANK],
            ['6hTd6d8d7d9d6s', [new Card('Td'), new Card('9d'), new Card('8d'), new Card('7d'), new Card('6d')], 'Td9d8d7d6d', 'Straight Flush, Ten to Six of Diamonds', ShortDeckHighHandEvaluator::STRAIGHT_FLUSH_RANK],
            ['7sJs9dJc7hJhJdAh', [new Card('Js'), new Card('Jh'), new Card('Jd'), new Card('Jc'), new Card('Ah')], 'JsJhJdJcAh', 'Four of a Kind, Jacks', ShortDeckHighHandEvaluator::FOUR_OF_A_KIND_RANK],
            ['AcKd9sTs8d6d9dTd', [new Card('Kd'), new Card('Td'), new Card('9d'), new Card('8d'), new Card('6d')], 'KdTd9d8d6d', 'Flush, King high of Diamonds', ShortDeckHighHandEvaluator::FLUSH_RANK],
            ['6dTd7c6h6sTs7h', [new Card('6s'), new Card('6h'), new Card('6d'), new Card('Ts'), new Card('Td')], '6s6h6dTsTd', 'Full House, Sixs full of Tens', ShortDeckHighHandEvaluator::FULL_HOUSE_RANK], //666TT55 given
            ['6dTd7c6h6sTsTh', [new Card('Ts'), new Card('Th'), new Card('Td'), new Card('6s'), new Card('6h')], 'TsThTd6s6h', 'Full House, Tens full of Sixs', ShortDeckHighHandEvaluator::FULL_HOUSE_RANK], //TTT6665 given
            ['Qc9sJd6cTc6d8s', [new Card('Qc'), new Card('Jd'), new Card('Tc'), new Card('9s'), new Card('8s')], 'QcJdTc9s8s', 'Straight, Queen to Eight', ShortDeckHighHandEvaluator::STRAIGHT_RANK],
            ['Qc8sKhJhAsJdJc', [new Card('Jh'), new Card('Jd'), new Card('Jc'), new Card('As'), new Card('Kh')], 'JhJdJcAsKh', 'Three of a Kind, Jacks', ShortDeckHighHandEvaluator::THREE_OF_A_KIND_RANK],
            ['6s8dQs7dKc8c6h', [new Card('8d'), new Card('8c'), new Card('6s'), new Card('6h'), new Card('Kc')], '8d8c6s6hKc', 'Two Pair, Eights and Sixs', ShortDeckHighHandEvaluator::TWO_PAIR_RANK],
            ['Tc9sAdQh8d6h8c', [new Card('8d'), new Card('8c'), new Card('Ad'), new Card('Qh'), new Card('Tc')], '8d8cAdQhTc', 'One Pair, Eights', ShortDeckHighHandEvaluator::ONE_PAIR_RANK],
            ['8d7sQhJs9cKd6h', [new Card('Kd'), new Card('Qh'), new Card('Js'), new Card('9c'), new Card('8d')], 'KdQhJs9c8d', 'High Card, King', ShortDeckHighHandEvaluator::HIGH_CARD_RANK],
        ];
    }
}