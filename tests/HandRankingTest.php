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

        $hand = new HandRanking([$KingS, $FiveD, $JackC, $JackS, $KingC, $JackH]);

        $expected = [
            11 => [$JackS, $JackH, $JackC],
            13 => [$KingS, $KingC],
            5 => [$FiveD],
        ];

        $this->assertEquals($expected, $hand->getValueHistogram());
    }

    /**
     * @test
     * @dataProvider fourOfAKinds
    */
    public function hand_ranking_is_a_four_of_a_kind($hand, $isFourOfAKind)
    {
        $hand = new HandRanking($hand);

        $this->assertEquals($hand->isFourOfAKind(), $isFourOfAKind);
    }

    public function fourOfAKinds() {
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
        $hand = new HandRanking($hand);

        $this->assertEquals($hand->isFullHouse(), $isFullHouse);
    }

    public function fullHouses() {
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

    /**
     * @test
     * @dataProvider straights
    */
    public function hand_ranking_is_a_straight($hand, $isStraight)
    {
        $hand = new HandRanking($hand);

        $this->assertEquals($hand->isStraight(), $isStraight);
    }

    public function straights() {

        // TODO: Need to test for Ace high  and Ace low straights

        return [
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
        $hand = new HandRanking($hand);

        $this->assertEquals($hand->isThreeOfAKind(), $isThreeOfAKind);
    }

    public function threeOfAKinds() {
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
        $hand = new HandRanking($hand);

        $this->assertEquals($hand->isTwoPair(), $isTwoPair);
    }

    public function twoPairs() {
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
        $hand = new HandRanking($hand);

        $this->assertEquals($hand->isOnePair(), $isOnePair);
    }

    public function onePairs() {
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
        $hand = new HandRanking($hand);

        $this->assertEquals($hand->isHighCard(), $isHighCard);
    }

    public function highCards() {
        return [
            ['Kh9c6h4sKd2sQh', false], // Two Pair
            ['Kh9c6h4s3d2sQh', true], // KQ964
            ['Kh9c4s3d2sQhJsAh', true], // AKQJ9
        ];
    }
}