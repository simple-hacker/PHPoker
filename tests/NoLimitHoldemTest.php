<?php

namespace simplehacker\PHPoker\Tests;

use simplehacker\PHPoker\Card;
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

    /** @test */
    public function can_set_community_cards_and_players_hole_cards_for_testing_only()
    {
        $communityCards = ['Ah', '3s', 'Kd', '9d', 'Qs']; 
        $playersWithHoleCards = [['5s', '5d'], ['9c', 'Tc']];

        // TestCase helper method for setting communityCards and player's hole cards
        // using RelectionClass to set protected properties
        // Used for testing hand winners are correct, because otherwise it would be random every test
        $hand = $this->createNoLimitHoldemHand($communityCards, $playersWithHoleCards);

        $mappedCommunityCards = array_map(fn($card) => new Card($card), $communityCards);
        $mappedPlayers = array_map(function($cards) {
            $player = new Player();
            $holeCards = array_map(fn($card) => new Card($card), $cards);
            $player->giveCards($holeCards);
            return $player;
        }, $playersWithHoleCards);

        $this->assertEquals($mappedCommunityCards, $hand->getCommunityCards());
        $this->assertEquals($mappedPlayers, $hand->getPlayers());
    }

    /**
     * @test
     * @dataProvider winners
     * */
    public function can_get_correct_winners_of_the_hand($communityCards, $players, $expectedWinnersKeys)
    {
        $hand = $this->createNoLimitHoldemHand($communityCards, $players);

        $allPlayers = $hand->getPlayers();
        $winners = [];

        foreach($expectedWinnersKeys as $playerKey) {
            $winners[$playerKey] = $allPlayers[$playerKey];
        }

        $this->assertEquals($winners, $hand->getWinners());
    }

    public function winners()
    {
        // [Array of community card values], [Array of players which holds an array of hole card values], [Keys of players expected to win]
        return [
            [['Ah', '3s', 'Kd', '9d', 'Qs'], [['4s', '5c'], ['Js', 'Td'], ['5s', '5d'], ['Jc', 'Tc']], [1, 3]], // Players 1 and 3 = AKQJT straight
            [['Ah', 'Js', 'Qd', 'Tc', 'Kd'], [['9c', 'Td'], ['4c', '4d'], ['8h', '8d']], [0, 1, 2]], // 3 way chop pot playing board
            [['Ah', '9s', '9h', '9c', 'Kh'], [['9d', 'Qd'], ['4h', '4c'], ['Qh', 'Th']], [0]], // 9999A > 99944 > AKQT9h
            [['Ah', '9s', '9h', '9c', 'Kh'], [['4h', '5h'], ['Qh', 'Th']], [1]], // AK954h < AKQT9h
            [['6h', '6s', '6d', '4c', '7h'], [['Kh', 'Qh'], ['Ad', 'Kc'], ['Jc', 'Ah']], [1]], // 666KQ < 666AK
            [['6h', '5s', '3d', '2c', 'Ah'], [['4h', '7h'], ['Ad', '4c']], [0]], // 765432 > 65432
            [['Kh', 'Ks', 'Kd', 'Ac', 'Qh'], [['9d', '9c'], ['Th', 'Tc']], [1]], // KKK99 < KKKTT
            [['Kh', 'Ks', 'Qd', 'Qc', '4h'], [['9d', '9c'], ['Ah', 'Th']], [1]], // KKQQ9 < KKQQA
            [['Kh', 'Ks', 'Qd', 'Qc', '4h'], [['9d', '9c'], ['Ah', 'Th'], ['Qh', 'Js']], [2]], // KKQQ9 < KKQQA < QQQKK
            [['Kh', 'Ks', 'Qd', 'Qs', '4h'], [['Kc', 'Qc'], ['Qh', 'Js']], [0]], // KKKQQ > QQQKK
            [['Kh', 'Ks', 'Qd', 'Qs', '4h'], [['Kc', 'Qc'], ['Qh', 'Kd']], [0, 1]], // KKKQQ = KKKQQ
            [['Qh', 'Tc', '8d', '4s', '3c'], [['2d', '9d'], ['9h', '6s'], ['7h', '2h']], [1]], // QT984 < QT986 > QT874
            [['8d', '6s', '3c', 'Qh', 'Tc'], [['7h', '2h'], ['2d', '9d'], ['9h', '5s']], [1, 2]], // QT876 < QT986 = QT986
            [['8c', '6s', '3c', 'Qc', 'Tc'], [['7c', '2h'], ['2d', '9d'], ['9h', '5s']], [0]], // QT876c > QT863c = QT863c
        ];
    }

    /** @test */
    public function cannot_get_winner_if_there_is_not_five_commmunity_cards()
    {
        $this->expectException(InvalidActionException::class);

        // Only four communityCards
        $communityCards = ['Ah', '3s', 'Kd', '9d'];
        $players = [['4s', '5c'], ['9s', 'Td'], ['5s', '5d'], ['9c', 'Tc']];

        $hand = $this->createNoLimitHoldemHand($communityCards, $players);

        $hand->getWinners();
    }

    /**
     * @test
     * @dataProvider winnerDescriptions
     * */
    public function the_players_best_hand_is_saved_to_players_during_showdown($communityCards, $players, $description, $shortDescription)
    {       
        // This is needed so we can access the players best hand description etc
        $hand = $this->createNoLimitHoldemHand($communityCards, $players);

        $winners = $hand->getWinners();
        $winner = reset($winners); // Just give the first winner when testing

        $handRanking = $winner->getHandRanking();

        $this->assertSame($description, $handRanking->getDescription());
        $this->assertSame($shortDescription, $handRanking->getShortDescription());
    }

    public function winnerDescriptions()
    {
        return [
            [['Ah', '9s', '9h', '9c', 'Kh'], [['4h', '5h'], ['Qh', 'Th']], 'Flush, Ace high of Hearts, with King+Queen+Ten+Nine kickers', 'AhKhQhTh9h'],
            [['6h', '5s', '3d', '2c', 'Ah'], [['4h', '7h'], ['Ad', '4c']], 'Straight, Seven to Three', '7h6h5s4h3d'],
            [['8h', '8s', '8d', '2c', '3h'], [['Ah', 'Kh'], ['Ad', 'Qc']], 'Three of a Kind, Eights, with Ace+King kickers', '8s8h8dAhKh'],
            [['Kh', 'Ks', 'Kd', 'Ac', 'Qh'], [['9d', '9c'], ['Th', 'Tc']], 'Full House, Kings full of Tens', 'KsKhKdThTc'],
            [['Qh', 'Tc', '8d', '4s', '3c'], [['2d', '9d'], ['9h', '6s'], ['7h', '2h']], 'High Card, Queen, with Ten+Nine+Eight+Six kickers', 'QhTc9h8d6s'],
            [['Qh', '6c', '5d', '4s', '3c'], [['Td', '9d'], ['9h', '8s']], 'High Card, Queen, with Ten+Nine+Six+Five kickers', 'QhTd9d6c5d'],
        ];
    }

    /**
     * @test
     * @dataProvider kickerDescriptions
     * */
    public function kicker_descriptions_for_all_players_when_needed($communityCards, $players, $descriptions)
    {
        $hand = $this->createNoLimitHoldemHand($communityCards, $players);
        // Generate handRankings and descriptions for all players still left in the pot
        $hand->showdown();
        $players = $hand->getPlayers();

        $handDescriptions = array_map(fn($player) => $player->getHandRanking()->getDescription(), $players);

        $this->assertSame($descriptions, $handDescriptions);
    }

    public function kickerDescriptions()
    {
        return [
            [
                ['Ah', '9s', '9h', '9c', 'Kh'],
                [['4h', '5h'], ['Qh', 'Th']],
                [
                    0 => 'Flush, Ace high of Hearts, with King+Nine+Five+Four kickers',
                    1 => 'Flush, Ace high of Hearts, with King+Queen+Ten+Nine kickers',
                ]
            ],
            [
                ['Ah', 'As', '9h', '8c', 'Qs'],
                [['Kh', 'Kd'], ['Qc', '9c'], ['Kc', 'Ks']],
                [
                    // No kickers needed and both AAKK had the same kicker Queen
                    0 => 'Two Pair, Aces and Kings',
                    1 => 'Two Pair, Aces and Queens',
                    2 => 'Two Pair, Aces and Kings',
                ]
            ],
            [
                ['Ah', 'As', '9h', '8c', 'Qs'],
                [['Qh', 'Kd'], ['Qc', '9c'], ['Qd', 'Ks']],
                [
                    // All three players have Aces and Queens
                    // But Players 0 and 2 have the best hand with the King kicker
                    // Set kickerDescriptions for all hands
                    0 => 'Two Pair, Aces and Queens, with King kicker',
                    1 => 'Two Pair, Aces and Queens, with Nine kicker',
                    2 => 'Two Pair, Aces and Queens, with King kicker',
                ]
            ],
            [
                ['Ah', 'As', 'Ad', 'Ac', 'Ts'],
                [['4h', '4d'], ['6c', '6s'], ['8d', '8s']],
                [
                    // Three way chop with AAAAT, don't include kickers
                    0 => 'Four of a Kind, Aces',
                    1 => 'Four of a Kind, Aces',
                    2 => 'Four of a Kind, Aces',
                ]
            ],
            [
                ['Ah', 'As', 'Ad', 'Ac', 'Ts'],
                [['4h', '4d'], ['6c', '6s'], ['8d', 'Ks']],
                [
                    // AAAAK > AAAAT so include kickers
                    0 => 'Four of a Kind, Aces, with Ten kicker',
                    1 => 'Four of a Kind, Aces, with Ten kicker',
                    2 => 'Four of a Kind, Aces, with King kicker',
                ]
            ],
            [
                ['6h', '6s', 'Kd', '2c', '2s'],
                [['Kc', '4s'], ['Ah', 'Ad'], ['Kh', 'Qs']],
                [
                    // All have two pairs, 0 and 2 both have KK66 where kicker plays
                    // Player 0 has KK664
                    // Player 1 has AA66K
                    // Player 2 has KK66Q
                    0 => 'Two Pair, Kings and Sixs, with Four kicker',
                    1 => 'Two Pair, Aces and Sixs',
                    2 => 'Two Pair, Kings and Sixs, with Queen kicker',
                ]
            ],
        ];
    }
}