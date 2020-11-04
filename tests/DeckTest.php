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
        
        $this->assertCount(52, $deck->cards);
        $this->assertSame(52, $deck->count());
    }

    /** @test */
    public function can_take_the_first_card_from_deck()
    {
        $deck = new Deck();

        $firstCard = $deck->cards[0];

        $card = $deck->takeCard();

        $this->assertNotContainsEquals($firstCard, $deck->cards);
        $this->assertEquals(51, $deck->count());
        $this->assertInstanceOf(Card::class, $card);
        $this->assertEquals($firstCard, $card);
    }

    /** @test */
    public function can_take_a_specific_card_from_deck()
    {
        $deck = new Deck();

        $fourHearts = new Card('4h');

        $card = $deck->takeCard($fourHearts);

        $this->assertNotContainsEquals($fourHearts, $deck->cards);
        $this->assertEquals(51, $deck->count());
        $this->assertInstanceOf(Card::class, $card);
        $this->assertEquals($fourHearts, $card);
    }

    /** @test */
    public function can_take_a_number_of_cards_from_the_deck()
    {
        $deck = new Deck();

        // Take 5 cards from the deck
        $cards = $deck->takeCards(5);

        $this->assertEquals(47, $deck->count());
        $this->assertContainsOnlyInstancesOf(Card::class, $cards);
    }

    /** @test */
    public function cannot_take_card_if_not_found_in_deck()
    {
        // TODO: Instead of exception maybe just return false?

        $this->expectException(InvalidDeckOperationException::class);

        $deck = new Deck();

        $fourHearts = new Card('4h');

        // Remove from deck
        $deck->takeCard($fourHearts);
        $this->assertNotContainsEquals($fourHearts, $deck->cards);

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
        $this->assertNotContainsEquals($fiveSpades, $deck->cards);
        
        $deck->addCard($fiveSpades);
        $this->assertContainsEquals($fiveSpades, $deck->cards);
    }

    /** @test */
    public function cannot_add_card_if_exist_in_deck()
    {
        $this->expectException(InvalidDeckOperationException::class);

        $deck = new Deck(false);
        
        $fiveDiamonds = new Card(5, 'Diamonds');
        
        // Assert it is in the deck
        $this->assertContainsEquals($fiveDiamonds, $deck->cards);

        // Try to add card again, expect exception
        $deck->addCard($fiveDiamonds);
        $this->assertContainsEquals($fiveDiamonds, $deck->cards);
    }
}