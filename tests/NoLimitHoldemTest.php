<?php

namespace simplehacker\PHPoker\Tests;

use simplehacker\PHPoker\Player;
use simplehacker\PHPoker\Games\NoLimitHoldem;
use simplehacker\PHPoker\Tests\PHPokerTestCase;
use simplehacker\PHPoker\Exceptions\HandException;
use simplehacker\PHPoker\Exceptions\InvalidActionException;

class NoLimitHoldemTest extends PHPokerTestCase
{
    /** @test */
    public function must_give_an_array_of_players_when_instantiating()
    {
        $this->expectException(HandException::class);

        $players = [new Player(), 'Not a player', new Player()];

        $hand = new NoLimitHoldem($players);
    }

    /** @test */
    public function there_must_be_at_least_two_players_in_the_hand()
    {
        $this->expectException(HandException::class);

        $players = [new Player()];

        $hand = new NoLimitHoldem($players);
    }

    /** @test */
    public function cannnot_exceed_maximum_number_of_players()
    {
        $this->expectException(HandException::class);

        // Create 11 Players
        $players = $this->createPlayers(11);

        $hand = new NoLimitHoldem($players);
    }

    /** @test */
    public function can_get_the_players_of_the_hand()
    {
        // Create 8 Players
        $players = $this->createPlayers(8);
        $hand = new NoLimitHoldem($players);

        $this->assertSame(8, count($hand->getPlayers()));
    }

    /** @test */
    public function can_deal_hand_limit_cards_to_players()
    {
        // No limit Holdem can only have two cards each.

        $players = $this->createPlayers(4);

        $hand = new NoLimitHoldem($players);

        $hand->deal();

        $players = $hand->getPlayers();

        foreach($players as $player) {
            $this->assertSame(2, $player->getHoleCardsCount());
        }
    }

    /** @test */
    public function cannot_deal_again_if_hand_has_already_been_dealt()
    {
        $this->expectException(InvalidActionException::class);

        $players = $this->createPlayers(4);

        $hand = new NoLimitHoldem($players);

        $hand->deal();

        // Try to deal hand again
        $hand->deal();
    }

    /** @test */
    public function a_flop_can_be_dealt()
    {
        $players = $this->createPlayers(4);

        $hand = new NoLimitHoldem($players);

        $hand->deal();

        $hand->flop();

        $this->assertCount(3, $hand->getCommunityCards());
    }

    /** @test */
    public function a_flop_cannot_be_dealt_if_player_deal_has_not_been_completed()
    {
        $this->expectException(InvalidActionException::class);

        $players = $this->createPlayers(4);

        $hand = new NoLimitHoldem($players);

        // Do not deal to players

        $hand->flop();
    }

    /** @test */
    public function cannot_deal_flop_twice_in_a_row()
    {
        $this->expectException(InvalidActionException::class);

        $players = $this->createPlayers(4);

        $hand = new NoLimitHoldem($players);

        $hand->deal();
        $hand->flop();
        $hand->flop();
    }

    /** @test */
    public function a_turn_can_be_dealt_if_flop_has_been_dealt()
    {
        $players = $this->createPlayers(4);

        $hand = new NoLimitHoldem($players);

        $hand->deal();
        $hand->flop();

        $hand->turn();

        $this->assertCount(4, $hand->getCommunityCards());
    }

    /** @test */
    public function a_turn_cannot_be_dealt_if_there_are_not_three_community_cards()
    {
        $this->expectException(InvalidActionException::class);

        $players = $this->createPlayers(4);

        $hand = new NoLimitHoldem($players);

        $hand->deal();
        // Do not deal flop
        $hand->turn();
    }

    /** @test */
    public function cannot_deal_turn_twice_in_a_row()
    {
        $this->expectException(InvalidActionException::class);

        $players = $this->createPlayers(4);

        $hand = new NoLimitHoldem($players);

        $hand->deal();
        $hand->flop();
        $hand->turn();
        $hand->turn();
    }

    /** @test */
    public function a_river_can_be_dealt_if_flop_and_turn_have_been_dealt()
    {
        $players = $this->createPlayers(4);

        $hand = new NoLimitHoldem($players);

        $hand->deal();
        $hand->flop();
        $hand->turn();

        $hand->river();

        $this->assertCount(5, $hand->getCommunityCards());
    }

    /** @test */
    public function a_river_cannot_be_dealt_if_there_are_not_four_community_cards()
    {
        $this->expectException(InvalidActionException::class);

        $players = $this->createPlayers(4);

        $hand = new NoLimitHoldem($players);

        $hand->deal();
        $hand->flop();
        // Do not deal turn
        $hand->river();
    }

    /** @test */
    public function cannot_deal_river_twice_in_a_row()
    {
        $this->expectException(InvalidActionException::class);

        $players = $this->createPlayers(4);

        $hand = new NoLimitHoldem($players);

        $hand->deal();
        $hand->flop();
        $hand->turn();
        $hand->river();
        $hand->river();
    }
}