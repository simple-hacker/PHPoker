<?php

namespace simplehacker\PHPoker\Tests;

use simplehacker\PHPoker\Card;
use simplehacker\PHPoker\Deck;
use PHPUnit\Framework\TestCase;
use simplehacker\PHPoker\Exceptions\InvalidDeckOperationException;

class DeckTest extends TestCase
{
    /** @test */
    public function deck_holds_52_cards()
    {
        $deck = new Deck();
        
        $this->assertCount(52, $deck->getCards());
        $this->assertSame(52, $deck->count());
        $this->assertContainsOnlyInstancesOf(Card::class, $deck->getCards());
    }

    /** @test */
    public function can_take_the_first_card_from_deck()
    {
        $deck = new Deck();

        $firstCard = $deck->getCards()[0];

        $card = $deck->takeCard();

        $this->assertNotContainsEquals($firstCard, $deck->getCards());
        $this->assertSame(51, $deck->count());
        $this->assertInstanceOf(Card::class, $card);
        $this->assertEquals($firstCard, $card);
    }

    /** @test */
    public function can_take_a_specific_card_from_deck()
    {
        $deck = new Deck();

        $fourHearts = new Card('4h');

        $card = $deck->takeCard($fourHearts);

        $this->assertNotContainsEquals($fourHearts, $deck->getCards());
        $this->assertSame(51, $deck->count());
        $this->assertInstanceOf(Card::class, $card);
        $this->assertEquals($fourHearts, $card);
    }

    /** @test */
    public function can_take_a_number_of_cards_from_the_deck()
    {
        $deck = new Deck();

        // Take 5 cards from the deck
        $cards = $deck->takeCards(5);

        $this->assertSame(47, $deck->count());
    }

    /** @test */
    public function removing_negative_or_zero_cards_only_removes_one_card()
    {
        $deck = new Deck();

        // 0 = 1
        $cards = $deck->takeCards(0);
        $this->assertSame(51, $deck->count());

        // 0 = 1
        $cards = $deck->takeCards(-1);
        $this->assertSame(50, $deck->count());
    }

    /** @test */
    public function cannot_remove_more_cards_than_there_are_in_the_deck()
    {
        $this->expectException(InvalidDeckOperationException::class);

        $deck = new Deck();

        // Take 53 cards from the deck
        $cards = $deck->takeCards(53);
    }

    /** @test */
    public function cannot_take_card_if_not_found_in_deck()
    {
        $this->expectException(InvalidDeckOperationException::class);

        $deck = new Deck();

        $fourHearts = new Card('4h');

        // Remove from deck
        $deck->takeCard($fourHearts);
        $this->assertNotContainsEquals($fourHearts, $deck->getCards());

        // Try to remove 4h again, exception is thrown
        $deck->takeCard($fourHearts);
    }

    /** @test */
    public function cannot_add_card_to_full_deck()
    {
        $this->expectException(InvalidDeckOperationException::class);
        
        $deck = new Deck();

        $card = new Card(1,1);

        $deck->addCard($card);
    }

    /** @test */
    public function can_add_card_if_not_exist_in_deck()
    {
        $deck = new Deck();

        $fiveSpades = new Card('5', 'spades');

        $deck->takeCard($fiveSpades);
        $this->assertNotContainsEquals($fiveSpades, $deck->getCards());
        
        $deck->addCard($fiveSpades);
        $this->assertContainsEquals($fiveSpades, $deck->getCards());
    }

    /** @test */
    public function cannot_add_card_if_exist_in_deck()
    {
        $this->expectException(InvalidDeckOperationException::class);

        $deck = new Deck(false);
        
        $fiveDiamonds = new Card(5, 'Diamonds');
        
        // Assert it is in the deck
        $this->assertContainsEquals($fiveDiamonds, $deck->getCards());

        // Try to add card again, expect exception
        $deck->addCard($fiveDiamonds);
        $this->assertContainsEquals($fiveDiamonds, $deck->getCards());
    }
}