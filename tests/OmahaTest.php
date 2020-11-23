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

        $this->assertEquals($omahaHand->getHand(), $hand);
        $this->assertEquals($omahaHand->getHandRank(), $handRank);
        $this->assertEquals($omahaHand->getShortDescription(), $handShortDescription);
        $this->assertEquals($omahaHand->getDescription(), $handDescription);
    }

    public function bestHands()
    {
        return [
            ['3hJdQdAdKd', '6s9d6cTd', [new Card('Kd'), new Card('Qd'), new Card('Jd'), new Card('Td'), new Card('9d')], 'KdQdJdTd9d', 'Straight Flush, King to Nine of Diamonds', HighHandEvaluator::STRAIGHT_FLUSH_RANK],
            ['7sJs9dJcJh', 'JdAhKsKd', [new Card('Js'), new Card('Jh'), new Card('Jd'), new Card('Jc'), new Card('Ah')], 'JsJhJdJcAh', 'Four of a Kind, Jacks', HighHandEvaluator::FOUR_OF_A_KIND_RANK],
            ['6dTh5c6h6s', 'Ts3c4s4d', [new Card('6s'), new Card('6h'), new Card('6d'), new Card('4s'), new Card('4d')], '6s6h6d4s4d', 'Full House, Sixs full of Fours', HighHandEvaluator::FULL_HOUSE_RANK], //TTT6665 given
            ['6dTh5c6h6s', 'TsTc4s4d', [new Card('Ts'), new Card('Th'), new Card('Tc'), new Card('6s'), new Card('6h')], 'TsThTc6s6h', 'Full House, Tens full of Sixs', HighHandEvaluator::FULL_HOUSE_RANK], //TTT6665 given
            ['Kd2d3s3h3d', '9dTd8s7s', [new Card('Kd'), new Card('Td'), new Card('9d'), new Card('3d'), new Card('2d')], 'KdTd9d3d2d', 'Flush, King high of Diamonds', HighHandEvaluator::FLUSH_RANK],
            ['Qc9sJdAcTc', '4d8sKd9c', [new Card('Kd'), new Card('Qc'), new Card('Jd'), new Card('Tc'), new Card('9c')], 'KdQcJdTc9c', 'Straight, King to Nine', HighHandEvaluator::STRAIGHT_RANK],
            ['AsAdAcAhKd', '9cTdQs6h', [new Card('As'), new Card('Ah'), new Card('Ad'), new Card('Qs'), new Card('Td')], 'AsAhAdQsTd', 'Three of a Kind, Aces', HighHandEvaluator::THREE_OF_A_KIND_RANK],
            ['6dTd5c6h6s', 'Ts5h3s9d', [new Card('6s'), new Card('6h'), new Card('6d'), new Card('Ts'), new Card('9d')], '6s6h6dTs9d', 'Three of a Kind, Sixs', HighHandEvaluator::THREE_OF_A_KIND_RANK],
            ['AdAcKsKcQh', '8h8c2s3d', [new Card('Ad'), new Card('Ac'), new Card('8h'), new Card('8c'), new Card('Ks')], 'AdAc8h8cKs', 'Two Pair, Aces and Eights', HighHandEvaluator::TWO_PAIR_RANK],
        ];
    }
}