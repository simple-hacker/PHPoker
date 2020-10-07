<?php

namespace SimpleHacker\PHPoker\Tests;

use SimpleHacker\PHPoker\Card;

use PHPUnit\Framework\TestCase;
use SimpleHacker\PHPoker\Exceptions\InvalidCardException;

class CardTest extends TestCase
{
    /**
    * @test
    * @dataProvider values
    */
    public function a_card_can_be_instantiated_with_valid_values($testValue, $value, $shortValue, $valueRank)
    {
        $card = new Card($testValue, 'spades');

        $this->assertEquals($value, $card->getValue());
        $this->assertEquals($shortValue, $card->getShortValue());
        $this->assertEquals($valueRank, $card->getValueRank());
    }

    /**
    * @test
    * @dataProvider suits
    */
    public function a_card_can_be_instantiated_with_valid_suits($testSuit, $suit, $shortSuit, $suitRank)
    {
        $card = new Card('A', $testSuit);

        $this->assertEquals($suit, $card->getSuit());
        $this->assertEquals($shortSuit, $card->getShortSuit());
        $this->assertEquals($suitRank, $card->getSuitRank());
    }

    /**
    * @test
    * @dataProvider descriptions
    */
    public function a_card_has_a_description($value, $suit, $description)
    {
        $card = new Card($value, $suit);

        $this->assertEquals($card->getDescription(), $description);
    }

    /**
    * @test
    * @dataProvider shortDescriptions
    */
    public function a_card_has_a_short_description($value, $suit, $description)
    {
        $card = new Card($value, $suit);

        $this->assertEquals($card->getShortDescription(), $description);
    }

    /**
    * @test
    * @dataProvider invalidCards
    */
    public function an_error_is_thrown_on_invalid_values_and_suits($value, $suit)
    {
        $this->expectException(InvalidCardException::class);

        $card = new Card($value, $suit);
    }

    public function values()
    {
        return [
            [1, 'Ace', 'A', 1],
            ['A', 'Ace', 'A', 1],
            ['Ace', 'Ace', 'A', 1],
            [2, 'Two', 2, 2],
            ['Two', 'Two', 2, 2],
            [3, 'Three', 3, 3],
            ['Three', 'Three', 3, 3],
            [4, 'Four', 4, 4],
            ['Four', 'Four', 4, 4],
            [5, 'Five', 5, 5],
            ['Five', 'Five', 5, 5],
            [6, 'Six', 6, 6],
            ['Six', 'Six', 6, 6],
            [7, 'Seven', 7, 7],
            ['Seven', 'Seven', 7, 7],
            [8, 'Eight', 8, 8],
            ['Eight', 'Eight', 8, 8],
            [9, 'Nine', 9, 9],
            ['Nine', 'Nine', 9, 9],
            [10, 'Ten', 'T', 10],
            ['T', 'Ten', 'T', 10],
            ['Ten', 'Ten', 'T', 10],
            [11, 'Jack', 'J', 11],
            ['J', 'Jack', 'J', 11],
            ['Jack', 'Jack', 'J', 11],
            [12, 'Queen', 'Q', 12],
            ['Q', 'Queen', 'Q', 12],
            ['Queen', 'Queen', 'Q', 12],
            [13, 'King', 'K', 13],
            ['K', 'King', 'K', 13],
            ['King', 'King', 'K', 13],
        ];
    }

    public function suits()
    {
        return [
            ['c', 'Clubs', 'c', 1],
            ['club', 'Clubs', 'c', 1],
            ['clubs', 'Clubs', 'c', 1],
            ['d', 'Diamonds', 'd', 2],
            ['diamond', 'Diamonds', 'd', 2],
            ['diamonds', 'Diamonds', 'd', 2],
            ['h', 'Hearts', 'h', 3],
            ['heart', 'Hearts', 'h', 3],
            ['hearts', 'Hearts', 'h', 3],
            ['s', 'Spades', 's', 4],
            ['spade', 'Spades', 's', 4],
            ['spades', 'Spades', 's', 4],
        ];
    }

    public function descriptions()
    {
        return [
            ['ace', 'spade', 'Ace of Spades'],
            ['Jack', 'diamonds', 'Jack of Diamonds'],
            [4, 'Hearts', 'Four of Hearts'],
            ['T', 'clubs', 'Ten of Clubs'],
        ];
    }

    public function shortDescriptions()
    {
        return [
            ['ace', 'spade', 'As'],
            ['Jack', 'diamonds', 'Jd'],
            [4, 'Hearts', '4h'],
            ['T', 'clubs', 'Tc'],
        ];
    }

    public function invalidCards()
    {
        return [
            // Invalid values
            // ['Acee', 's'],
            // ['B', 's'],
            // [-1, 's'],
            // [0, 's'],
            // [14, 's'],
            // [15, 's'],
            // Invalid suits
            ['A', 'heartss'],
            // ['A', 0],
            // ['A', -1],
            // ['A', 5],
            // ['A', 'X'],
            // ['A', 'B'],
        ];
    }
}