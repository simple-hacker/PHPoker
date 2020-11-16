<?php

namespace simplehacker\PHPoker\Tests;

use simplehacker\PHPoker\Card;
use simplehacker\PHPoker\HighHandEvaluator;
use simplehacker\PHPoker\Tests\PHPokerTestCase;

class HighHandEvaluatorTest extends PHPokerTestCase
{
    /** @test */
    public function cards_are_grouped_and_sorted_by_value()
    {
        // Three Jacks, Two Kings, One Five
        $JackS = new Card('Js');
        $KingC = new Card('Kc');
        $JackC = new Card('Jc');
        $JackH = new Card('Jh');
        $FiveD = new Card('5d');
        $KingS = new Card('Ks');

        $hand = new HighHandEvaluator([$KingS, $FiveD, $JackC, $JackS, $KingC, $JackH]);
        $reflectionHand = new \ReflectionClass($hand);
        $property = $reflectionHand->getProperty('valueHistogram');
        $property->setAccessible(true);

        $expected = [
            11 => [$JackS, $JackH, $JackC],
            13 => [$KingS, $KingC],
            5 => [$FiveD],
        ];

        $this->assertEquals($expected, $property->getValue($hand));
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

        $hand = new HighHandEvaluator([$FourD, $FiveS, $ThreeH, $AceH, $TwoH]);
        $reflectionHand = new \ReflectionClass($hand);
        $property = $reflectionHand->getProperty('suitHistogram');
        $property->setAccessible(true);

        $expected = [
            3 => [$AceH, $ThreeH, $TwoH],
            4 => [$FiveS],
            2 => [$FourD]
        ];

        // Note if this fails in the future it could be to do with Ace value rank being 1 instead of 14
        $this->assertEquals($expected, $property->getValue($hand));
    }

    /**
    * @test
    * @dataProvider royalFlushes
    */
    public function hand_ranking_is_a_royal_flush($hand, $isRoyalFlush)
    {
        $hand = new HighHandEvaluator($hand);

        $this->assertEquals($hand->isRoyalFlush(), $isRoyalFlush);
    }

    public function royalFlushes()
    {
        return [
            ['AcQcTcKcJc', true], // AKQJTc
            ['AcQcTcKcJc9c8c', true], // AKQJTc
            ['7h3h6h5h4h', false], // Only a straight flush
        ];
    }

    /**
    * @test
    * @dataProvider straightFlushes
    */
    public function hand_ranking_is_a_straight_flush($hand, $isStraightFlush)
    {
        $hand = new HighHandEvaluator($hand);

        $this->assertEquals($hand->isStraightFlush(), $isStraightFlush);
    }

    public function straightFlushes()
    {
        return [
            ['7h3h6h5h4h', true], // 76543h
            ['QdTd4s9dJd8h8d', true], // QJT98d
            ['Th9h8h6h5h', false], // Is a flush but missing gutshot for straight flush
        ];
    }

    /**
    * @test
    * @dataProvider fourOfAKinds
    */
    public function hand_ranking_is_a_four_of_a_kind($hand, $isFourOfAKind)
    {
        $hand = new HighHandEvaluator($hand);

        $this->assertEquals($hand->isFourOfAKind(), $isFourOfAKind);
    }

    public function fourOfAKinds()
    {
        return [
            ['Jh8hJsJcJd', true], // JJJJ8
            ['6sKhKc9sKsKd', true], // KKKK9
            ['Kh9c9d2s9h', false], // Three of a kind
        ];
    }

    /**
    * @test
    * @dataProvider fullHouses
    */
    public function hand_ranking_is_a_full_house($hand, $isFullHouse)
    {
        $hand = new HighHandEvaluator($hand);

        $this->assertEquals($hand->isFullHouse(), $isFullHouse);
    }

    public function fullHouses()
    {
        return [
            ['Jh8hJsJcJd', false], // Four of a kind
            ['9sKhKc9c3sKd', true], // KKK99
            ['5h9s5d5s9cKhKs', true], // 555KK
            ['Kh9c9d2s9h', false], // Three of a kind, not full house
        ];
    }

    /**
    * @test
    * @dataProvider flushes
    */
    public function hand_ranking_is_a_flush($hand, $isFlush)
    {
        $hand = new HighHandEvaluator($hand);

        $this->assertEquals($hand->isFlush(), $isFlush);
    }

    public function flushes()
    {
        return [
            ['7h3s8h2hKh4dTh', true],
            ['3s4s5s6sKs', true],
            ['2d3s4s8c9c', false],
        ];
    }

    /**
    * @test
    * @dataProvider straights
    */
    public function hand_ranking_is_a_straight($hand, $isStraight)
    {
        $hand = new HighHandEvaluator($hand);

        $this->assertEquals($hand->isStraight(), $isStraight);
    }

    public function straights()
    {
        return [
            ['AhTsQcJcKd', true], // AKQJT Broadway straight, A high
            ['7hAh5s3c2c4d', true], // 54321 Wheel straight, A low
            ['8h9s6c7cTs', true], // T9876
            ['3s4s5s6s2s7h8d', true], // 87654(32)
            ['3s6s5s4s2sQhKd', true], // 65432
            ['KsThQh9d8d7d', false], // High card, missing gutshot
        ];
    }

    /**
    * @test
    * @dataProvider threeOfAKinds
    */
    public function hand_ranking_is_a_three_of_a_kind($hand, $isThreeOfAKind)
    {
        $hand = new HighHandEvaluator($hand);

        $this->assertEquals($hand->isThreeOfAKind(), $isThreeOfAKind);
    }

    public function threeOfAKinds()
    {
        return [
            ['Jh8hJsJcJd', false], // Four of a kind
            ['9sKhKc9c3sKd', false], // Full house
            ['Ks3s9h3c3hQcTs', true], // 333KQ
            ['Kh9c9d2s9h', true], // 999K2
        ];
    }

    /**
    * @test
    * @dataProvider twoPairs
    */
    public function hand_ranking_is_two_pair($hand, $isTwoPair)
    {
        $hand = new HighHandEvaluator($hand);

        $this->assertEquals($hand->isTwoPair(), $isTwoPair);
    }

    public function twoPairs()
    {
        return [
            ['Ks3s9h3c3hQcTs', false], // Three of a kind
            ['Kh9cKd2s9h', true], // KK992
            ['Qs4h5h4s5cTsTc', true], // TT55Q (technically three pairs TT5544)
            ['Kh9cKd2sQh', false], // Only one pair KKQ92
        ];
    }

    /**
    * @test
    * @dataProvider onePairs
    */
    public function hand_ranking_is_one_pair($hand, $isOnePair)
    {
        $hand = new HighHandEvaluator($hand);

        $this->assertEquals($hand->isOnePair(), $isOnePair);
    }

    public function onePairs()
    {
        return [
            ['Qs4h5h4s5cTsTc', false], // Two pairs
            ['Kh9cKd2sQh', true], // KKQ92
            ['Kh9c6h4sKd2sQh', true], // KKQ96
            ['Kh9c6h4s3d2sQh', false], // High card
        ];
    }

    /**
    * @test
    * @dataProvider highCards
    */
    public function hand_ranking_is_high_card($hand, $isHighCard)
    {
        $hand = new HighHandEvaluator($hand);

        $this->assertEquals($hand->isHighCard(), $isHighCard);
    }

    public function highCards()
    {
        return [
            ['Kh9c6h4sKd2sQh', false], // Two Pair
            ['Kh9c6h4s3d2sQh', true], // KQ964
            ['Kh9c4s3d2sQhJsAh', true], // AKQJ9
        ];
    }

    /**
    * @test
    * @dataProvider bestHands
    */
    public function hand_ranking_gives_best_five_cards($cards, $hand, $handShortDescription, $handDescription, $handRank)
    {
        $handRanking = new HighHandEvaluator($cards);
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
            ['3hJdQdAdKdTd6s', [new Card('Ad'), new Card('Kd'), new Card('Qd'), new Card('Jd'), new Card('Td')], 'AdKdQdJdTd', 'Royal Flush, Ace to Ten of Diamonds', HighHandEvaluator::ROYAL_FLUSH_RANK],
            ['3hTd6d8d7d9d6s', [new Card('Td'), new Card('9d'), new Card('8d'), new Card('7d'), new Card('6d')], 'Td9d8d7d6d', 'Straight Flush, Ten to Six of Diamonds', HighHandEvaluator::STRAIGHT_FLUSH_RANK],
            ['7sJs9dJc7hJhJdAh', [new Card('Js'), new Card('Jh'), new Card('Jd'), new Card('Jc'), new Card('Ah')], 'JsJhJdJcAh', 'Four of a Kind, Jacks', HighHandEvaluator::FOUR_OF_A_KIND_RANK],
            ['6dTd5c6h6sTs5h', [new Card('6s'), new Card('6h'), new Card('6d'), new Card('Ts'), new Card('Td')], '6s6h6dTsTd', 'Full House, Sixs full of Tens', HighHandEvaluator::FULL_HOUSE_RANK], //666TT55 given
            ['6dTd5c6h6sTsTh', [new Card('Ts'), new Card('Th'), new Card('Td'), new Card('6s'), new Card('6h')], 'TsThTd6s6h', 'Full House, Tens full of Sixs', HighHandEvaluator::FULL_HOUSE_RANK], //TTT6665 given
            ['AcKd2d4s8d3d9dTd', [new Card('Kd'), new Card('Td'), new Card('9d'), new Card('8d'), new Card('3d')], 'KdTd9d8d3d', 'Flush, King high of Diamonds', HighHandEvaluator::FLUSH_RANK],
            ['Qc9sJd4cTc4d8s', [new Card('Qc'), new Card('Jd'), new Card('Tc'), new Card('9s'), new Card('8s')], 'QcJdTc9s8s', 'Straight, Queen to Eight', HighHandEvaluator::STRAIGHT_RANK],
            ['Qc8sKhJhAsJdJc', [new Card('Jh'), new Card('Jd'), new Card('Jc'), new Card('As'), new Card('Kh')], 'JhJdJcAsKh', 'Three of a Kind, Jacks', HighHandEvaluator::THREE_OF_A_KIND_RANK],
            ['3s8dQs4dKc8c4h', [new Card('8d'), new Card('8c'), new Card('4h'), new Card('4d'), new Card('Kc')], '8d8c4h4dKc', 'Two Pair, Eights and Fours', HighHandEvaluator::TWO_PAIR_RANK],
            ['Tc3sAdQh4d6h4c', [new Card('4d'), new Card('4c'), new Card('Ad'), new Card('Qh'), new Card('Tc')], '4d4cAdQhTc', 'One Pair, Fours', HighHandEvaluator::ONE_PAIR_RANK],
            ['3sQh4hTc8sKd6d5c', [new Card('Kd'), new Card('Qh'), new Card('Tc'), new Card('8s'), new Card('6d')], 'KdQhTc8s6d', 'High Card, King', HighHandEvaluator::HIGH_CARD_RANK],
        ];
    }
}