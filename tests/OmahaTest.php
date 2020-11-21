<?php

namespace simplehacker\PHPoker\Tests;

use simplehacker\PHPoker\Card;
use PHPUnit\Framework\TestCase;
use simplehacker\PHPoker\Hands\OmahaHand;
use simplehacker\PHPoker\Evaluators\HighHandEvaluator;

class OmahaTest extends TestCase
{
    /**
    * @test
    * @dataProvider bestHands
    */
    public function hand_ranking_gives_best_five_cards($communityCards, $holeCards, $hand, $handShortDescription, $handDescription, $handRank)
    {
        $omahaHand = new OmahaHand($communityCards, $holeCards);

        // $this->assertEquals($omahaHand->getHand(), $hand);
        $this->assertEquals($omahaHand->getHandRank(), $handRank);
        $this->assertEquals($omahaHand->getShortDescription(), $handShortDescription);
        $this->assertEquals($omahaHand->getDescription(), $handDescription);
    }

    public function bestHands()
    {
        return [
            ['3hJdQdAdKd', '6s9d6cTd', [new Card('Kd'), new Card('Qd'), new Card('Jd'), new Card('Td'), new Card('9d')], 'KdQdJdTd9d', 'Straight Flush, King to Nine of Diamonds', HighHandEvaluator::STRAIGHT_FLUSH_RANK],
            ['7sJs9dJcJh', 'JdAhKsKd', [new Card('Js'), new Card('Jh'), new Card('Jd'), new Card('Jc'), new Card('Ah')], 'JsJhJdJcAh', 'Four of a Kind, Jacks', HighHandEvaluator::FOUR_OF_A_KIND_RANK],
            ['AsAdAcAhKd', '9cTdQs6h', [new Card('As'), new Card('Ah'), new Card('Ad'), new Card('Qs'), new Card('Td')], 'AsAhAdQsTd', 'Three of a Kind, Aces', HighHandEvaluator::THREE_OF_A_KIND_RANK],
            ['6dTd5c6h6s', 'Ts5h3s9d', [new Card('6s'), new Card('6h'), new Card('6d'), new Card('Ts'), new Card('9d')], '6s6h6dTs9d', 'Three of a Kind, Sixs', HighHandEvaluator::THREE_OF_A_KIND_RANK],
            // ['6dTd5c6h6s', 'TsTh', [new Card('Ts'), new Card('Th'), new Card('Td'), new Card('6s'), new Card('6h')], 'TsThTd6s6h', 'Full House, Tens full of Sixs', HighHandEvaluator::FULL_HOUSE_RANK], //TTT6665 given
            // ['AcKd2d4s8d3d', '9dTd', [new Card('Kd'), new Card('Td'), new Card('9d'), new Card('8d'), new Card('3d')], 'KdTd9d8d3d', 'Flush, King high of Diamonds', HighHandEvaluator::FLUSH_RANK],
            // ['Qc9sJd4cTc', '4d8s', [new Card('Qc'), new Card('Jd'), new Card('Tc'), new Card('9s'), new Card('8s')], 'QcJdTc9s8s', 'Straight, Queen to Eight', HighHandEvaluator::STRAIGHT_RANK],
            // ['Qc8sKhJhAs', 'JdJc', [new Card('Jh'), new Card('Jd'), new Card('Jc'), new Card('As'), new Card('Kh')], 'JhJdJcAsKh', 'Three of a Kind, Jacks', HighHandEvaluator::THREE_OF_A_KIND_RANK],
            // ['3s8dQs4dKc', '8c4h', [new Card('8d'), new Card('8c'), new Card('4h'), new Card('4d'), new Card('Kc')], '8d8c4h4dKc', 'Two Pair, Eights and Fours', HighHandEvaluator::TWO_PAIR_RANK],
            // ['Tc3sAdQh4d', '6h4c', [new Card('4d'), new Card('4c'), new Card('Ad'), new Card('Qh'), new Card('Tc')], '4d4cAdQhTc', 'One Pair, Fours', HighHandEvaluator::ONE_PAIR_RANK],
            // ['3sQh4hTc8sKd', '6d5c', [new Card('Kd'), new Card('Qh'), new Card('Tc'), new Card('8s'), new Card('6d')], 'KdQhTc8s6d', 'High Card, King', HighHandEvaluator::HIGH_CARD_RANK],
        ];
    }
}