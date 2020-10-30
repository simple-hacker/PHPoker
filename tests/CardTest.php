<?php

namespace simplehacker\PHPoker\Tests;

use simplehacker\PHPoker\Card;

use PHPUnit\Framework\TestCase;
use simplehacker\PHPoker\Exceptions\InvalidCardException;

class CardTest extends TestCase
{
    /** @test */
    public function test()
    {
        $card = new Card('Jh');

        $this->assertEquals(11, $card->getValueRank());
    }

    /**
    * @test
    * @dataProvider values
    */
    public function card_can_be_instantiated_with_valid_values($testValue, $value, $shortValue, $valueRank)
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
    public function card_can_be_instantiated_with_valid_suits($testSuit, $suit, $shortSuit, $suitRank)
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
    public function card_has_a_description($value, $suit, $description)
    {
        $card = new Card($value, $suit);

        $this->assertEquals($card->getDescription(), $description);
    }

    /**
    * @test
    * @dataProvider shortDescriptions
    */
    public function card_has_a_short_description($value, $suit, $description)
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

    /**
    * @test
    * @dataProvider shortValues
    */
    public function card_can_be_instantiated_with_short_values($shortValue, $value, $suit, $valueRank, $suitRank)
    {
        $card = new Card($shortValue);

        $this->assertEquals($card->getValue(), $value);
        $this->assertEquals($card->getSuit(), $suit);
        $this->assertEquals($card->getValueRank(), $valueRank);
        $this->assertEquals($card->getSuitRank(), $suitRank);
    }

    /**
    * @test
    * @dataProvider invalidShortValues
    */
    public function an_error_is_thrown_on_invalid_short_values($shortValue)
    {
        $this->expectException(InvalidCardException::class);

        $card = new Card($shortValue);
    }

    public function values()
    {
        return [
            // Ace can be instantiated with either 1 or 14, but value is always 14 for high card
            [1, 'Ace', 'A', 14],
            [14, 'Ace', 'A', 14],
            ['A', 'Ace', 'A', 14],
            ['Ace', 'Ace', 'A', 14],
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
            ['Acee', 's'],
            ['B', 's'],
            [-1, 's'],
            [0, 's'],
            [15, 's'],
            ['', 's'],
            // Invalid suits
            ['A', 'heartss'],
            ['A', 0],
            ['A', -1],
            ['A', 5],
            ['A', 'X'],
            ['A', 'B'],
            ['A', ''],
        ];
    }

    public function shortValues()
    {
        return [
            ['4c', 'Four', 'Clubs', 4, 1],
            ['Jd', 'Jack', 'Diamonds', 11, 2],
            ['Ah', 'Ace', 'Hearts', 14, 3],
            ['7s', 'Seven', 'Spades', 7, 4],
        ];
    }
    
    public function invalidShortValues()
    {
        return [
            ['0d'],
            ['0dxx'],
            ['Dd'],
            ['KH'],
            ['js'],
            ['4r'],
            ['4S'],
        ];
    }
}