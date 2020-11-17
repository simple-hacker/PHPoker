<?php

namespace simplehacker\PHPoker\Tests;

use simplehacker\PHPoker\Card;
use simplehacker\PHPoker\Hands\TexasHoldemHand;
use simplehacker\PHPoker\Evaluators\HighHandEvaluator;

class TexasHoldemTest extends PHPokerTestCase
{
    /** @test */
    public function a_nlh_hand_can_be_instantiated_with_a_string_of_cards()
    {
        $communityCards = '9h3s9cJsKs';
        $holeCards = 'KhJc';

        $hand = new TexasHoldemHand($communityCards, $holeCards);

        $this->assertEquals([new Card('Ks'), new Card('Kh'), new Card('Js'), new Card('Jc'), new Card('9h')], $hand->getHand());
        $this->assertEquals('KsKhJsJc9h', $hand->getShortDescription());
    }

    /** @test */
    public function a_nlh_hand_can_be_instantiated_with_an_array_of_cards()
    {
        $communityCards = [new Card('9h'), '3s', new Card('9c'), new Card('Js'), new Card('Ks')];
        $holeCards = ['Kh', new Card('Jc')];

        $hand = new TexasHoldemHand($communityCards, $holeCards);

        $this->assertEquals([new Card('Ks'), new Card('Kh'), new Card('Js'), new Card('Jc'), new Card('9h')], $hand->getHand());
        $this->assertEquals('KsKhJsJc9h', $hand->getShortDescription());
    }

    /**
    * @test
    * @dataProvider bestHands
    */
    public function hand_ranking_gives_best_five_cards($communityCards, $holeCards, $hand, $handShortDescription, $handDescription, $handRank)
    {
        $noLimitHand = new TexasHoldemHand($communityCards, $holeCards);

        $this->assertEquals($noLimitHand->getHand(), $hand);
        $this->assertEquals($noLimitHand->getHandRank(), $handRank);
        $this->assertEquals($noLimitHand->getShortDescription(), $handShortDescription);
        $this->assertEquals($noLimitHand->getDescription(), $handDescription);
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
            ['3hJdQdAdKd', 'Td6s', [new Card('Ad'), new Card('Kd'), new Card('Qd'), new Card('Jd'), new Card('Td')], 'AdKdQdJdTd', 'Royal Flush, Ace to Ten of Diamonds', HighHandEvaluator::ROYAL_FLUSH_RANK],
            ['3hTd6d8d7d', '9d6s', [new Card('Td'), new Card('9d'), new Card('8d'), new Card('7d'), new Card('6d')], 'Td9d8d7d6d', 'Straight Flush, Ten to Six of Diamonds', HighHandEvaluator::STRAIGHT_FLUSH_RANK],
            ['7sJs9dJc7hJh', 'JdAh', [new Card('Js'), new Card('Jh'), new Card('Jd'), new Card('Jc'), new Card('Ah')], 'JsJhJdJcAh', 'Four of a Kind, Jacks', HighHandEvaluator::FOUR_OF_A_KIND_RANK],
            ['6dTd5c6h6s', 'Ts5h', [new Card('6s'), new Card('6h'), new Card('6d'), new Card('Ts'), new Card('Td')], '6s6h6dTsTd', 'Full House, Sixs full of Tens', HighHandEvaluator::FULL_HOUSE_RANK], //666TT55 given
            ['6dTd5c6h6s', 'TsTh', [new Card('Ts'), new Card('Th'), new Card('Td'), new Card('6s'), new Card('6h')], 'TsThTd6s6h', 'Full House, Tens full of Sixs', HighHandEvaluator::FULL_HOUSE_RANK], //TTT6665 given
            ['AcKd2d4s8d3d', '9dTd', [new Card('Kd'), new Card('Td'), new Card('9d'), new Card('8d'), new Card('3d')], 'KdTd9d8d3d', 'Flush, King high of Diamonds', HighHandEvaluator::FLUSH_RANK],
            ['Qc9sJd4cTc', '4d8s', [new Card('Qc'), new Card('Jd'), new Card('Tc'), new Card('9s'), new Card('8s')], 'QcJdTc9s8s', 'Straight, Queen to Eight', HighHandEvaluator::STRAIGHT_RANK],
            ['Qc8sKhJhAs', 'JdJc', [new Card('Jh'), new Card('Jd'), new Card('Jc'), new Card('As'), new Card('Kh')], 'JhJdJcAsKh', 'Three of a Kind, Jacks', HighHandEvaluator::THREE_OF_A_KIND_RANK],
            ['3s8dQs4dKc', '8c4h', [new Card('8d'), new Card('8c'), new Card('4h'), new Card('4d'), new Card('Kc')], '8d8c4h4dKc', 'Two Pair, Eights and Fours', HighHandEvaluator::TWO_PAIR_RANK],
            ['Tc3sAdQh4d', '6h4c', [new Card('4d'), new Card('4c'), new Card('Ad'), new Card('Qh'), new Card('Tc')], '4d4cAdQhTc', 'One Pair, Fours', HighHandEvaluator::ONE_PAIR_RANK],
            ['3sQh4hTc8sKd', '6d5c', [new Card('Kd'), new Card('Qh'), new Card('Tc'), new Card('8s'), new Card('6d')], 'KdQhTc8s6d', 'High Card, King', HighHandEvaluator::HIGH_CARD_RANK],
        ];
    }
}