<?php

namespace simplehacker\PHPoker\Tests;

use simplehacker\PHPoker\Card;
use simplehacker\PHPoker\Player;
use simplehacker\PHPoker\Tests\PHPokerTestCase;
use simplehacker\PHPoker\Exceptions\InvalidPlayerOperationException;

class PlayerTest extends PHPokerTestCase
{
    /** @test */
    public function a_player_can_be_given_cards()
    {
        $player = new Player();
        $cards = [new Card('Ah'), new Card('As')];

        $player->giveCards($cards);

        $this->assertEquals($player->getHoleCards(), $cards);
        $this->assertCount(2, $player->getHoleCards());
    }

    /** @test */
    public function each_element_of_a_card_array_must_be_an_instance_of_card()
    {
        $this->expectException(InvalidPlayerOperationException::class);

        $player = new Player();
        $cards = [new Card('Ah'), 'As', new Card('Ad')];

        $player->giveCards($cards);

        $this->assertCount(0, $player->getHoleCards());
    }

    /** @test */
    public function can_keep_adding_cards_to_players_hole_cards()
    {
        // To confirm array splat operator is working
        $player = new Player();
        $aces = [$aceH, $aceS] = [new Card('Ah'), new Card('As')];
        $kings = [$kingH, $kingS] = [new Card('Kh'), new Card('Ks')];

        $player->giveCards($aces);
        $player->giveCards($kings);

        $this->assertCount(4, $player->getHoleCards());
        $this->assertEquals([$aceH, $aceS, $kingH, $kingS], $player->getHoleCards());
    }

    /** @test */
    public function a_single_card_can_be_given_instead_of_an_array_of_cards()
    {
        $player = new Player();
        $player->giveCard(new Card('Ah'));

        $this->assertCount(1, $player->getHoleCards());
        $this->assertEquals([new Card('Ah')], $player->getHoleCards());
    }

    /** @test */
    public function can_give_player_cards_and_then_a_single_card()
    {
        $player = new Player();
        $aces = [$aceH, $aceS] = [new Card('Ah'), new Card('As')];
        $aceC = new Card('Ac');
        $player->giveCards($aces);
        $player->giveCard($aceC);

        $this->assertCount(3, $player->getHoleCards());
        $this->assertEquals([$aceH, $aceS, $aceC], $player->getHoleCards());
    }

    /** @test */
    public function an_empty_array_doesnt_affect_the_hole_cards()
    {
        $player = new Player();
        $aces = [$aceH, $aceS] = [new Card('Ah'), new Card('As')];

        $player->giveCards($aces);
        $player->giveCards([]);

        $this->assertCount(2, $player->getHoleCards());
        $this->assertEquals([$aceH, $aceS], $player->getHoleCards());
    }
}