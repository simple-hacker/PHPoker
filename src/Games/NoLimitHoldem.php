<?php

namespace simplehacker\PHPoker\Games;

use simplehacker\PHPoker\Deck;
use simplehacker\PHPoker\Player;
use simplehacker\PHPoker\Exceptions\HandException;
use simplehacker\PHPoker\Exceptions\InvalidActionException;
use simplehacker\PHPoker\HandRanking;

class NoLimitHoldem
{
    /**
     * The table limit of players in a hand
     * 
     * @var const
     */
    const TABLE_LIMIT = 10;

    /**
     * The limit of hole cards a player can have
     * 
     * @var const
     */
    const HOLE_CARD_LIMIT = 2;

    /**
     * Array of Players in the hand
     * 
     * @var array
     */
    protected $players = [];

    /**
     * The suffled deck of cards for the hand
     * 
     * @var Deck
     */
    protected $deck;

    /**
     * The community cards of the hand
     * 
     * @var array
     */
    protected $communityCards = [];

    /**
     * Boolean if the hand has already been dealt to players
     * 
     * @var bool
     */
    protected $dealt = false;

    /**
    * Instantiate the NoLimitHoldem class
    * Must give an array of between two and table limit Player instances, 
    * 
    * @param array $players
    * @return void
    */
    public function __construct(array $players)
    {
        // Check min and max here
        if (count($players) < 2 || count($players) > self::TABLE_LIMIT) {
            throw new HandException('Array must contain between two and table limit instances of Player');
        }

       foreach ($players as $player) {
           if (! ($player instanceof Player)) {
               throw new HandException('Array must only contain instances of Player');
           }
       }

       $this->players = $players;
       $this->deck = new Deck();
    }

    /**
    * Returns the array of Players
    * 
    * @return array
    */
    public function getPlayers(): Array
    {
        return $this->players;
    }

    /**
    * Give n number of cards to Player
    * 
    * @param Player $player
    * @param int $numberofCards
    * @return void
    */
    protected function giveCardsToPlayer(Player $player, int $numberOfCards = 1)
    {
        if (($player->getHoleCardsCount() + $numberOfCards) > self::HOLE_CARD_LIMIT) {
            throw new HandException('Player will exceed the hole card limit');
        }

        $cards = $this->deck->takeCards($numberOfCards);

        $player->giveCards($cards);
    }

    /**
    * Add a number of cards to the community cards
    * 
    * @param int $numberofCards
    * @return void
    */
    protected function addCommunityCards(int $numberOfCards = 1)
    {
        $cards = $this->deck->takeCards($numberOfCards);

        $this->communityCards = [...$this->communityCards, ...$cards];
    }

    /**
    * Returns the community cards
    * 
    * @return array
    */
    public function getCommunityCards(): Array
    {
        return $this->communityCards;
    }

    /**
    * Deal two Cards to each Player
    * 
    * @return void
    */
    public function deal()
    {
        if ($this->dealt) {
            throw new InvalidActionException('Hand has already been dealt');
        }

        foreach ($this->players as $player) {
            $this->giveCardsToPlayer($player, 2);
        }

        $this->dealt = true;
    }

    /**
    * Deal the flop to the community cards
    * 
    * @return void
    */
    public function flop()
    {
        if (! $this->dealt) {
            throw new InvalidActionException('Hand has not been dealt');
        }

        if (count($this->communityCards) !== 0) {
            throw new InvalidActionException('Invalid community card dealing sequence');
        }

        $this->addCommunityCards(3);
    }

    /**
    * Deal the turn to the community cards
    * 
    * @return void
    */
    public function turn()
    {
        if (count($this->communityCards) !== 3) {
            throw new InvalidActionException('Invalid community card dealing sequence');
        }

        $this->addCommunityCards(1);
    }

    /**
    * Deal the river to the community cards
    * 
    * @return void
    */
    public function river()
    {
        if (count($this->communityCards) !== 4) {
            throw new InvalidActionException('Invalid community card dealing sequence');
        }

        $this->addCommunityCards(1);
    }

    /**
    * Returns an array of Players that won the hand.
    * 
    * @return array
    */
    public function getWinners(): Array
    {
        // NOTE:  ^^^ This will eventually accept and array of players, so when dealing with pots
        // Will loop through each pot and send an array of players entitled to each pot to determine who wins it.
        // This will also still calculate whether we need kicker descriptions for each pot still

        // Cannot get winners if the river has not been dealt
        // TODO: Also check that all betting ceases (when working with pots)
        if (count($this->communityCards) !== 5) {
            throw new InvalidActionException('Cannot get winners until there are five community cards');
        }

        $handRankings = [];
        $bestHandValue = 0;
        $bestHandTypeValue = 0;
        $numberOfHandsDeterminedByKickers = 0;

        // Loop through all players and combine communityCard with player's holeCards (allCards)
        // Get the best hand for each player using allCards
        // Get the hand ranking value of each handRank, setting the best hand rank value along the way
        foreach ($this->players as $playerIndex => $player) {

            // NOTE: When dealing with pots, check to see if player has already built up their hand ranking to save calculating again
            // if ($player->getHandRanking()) {
            //      $handRanking = $player->getHandRanking();
            // } else {
            //
                $allCards = [...$this->communityCards, ...$player->getHoleCards()];
                $handRanking = new HandRanking($allCards);
                $player->setHandRanking($handRanking);
            // }
            
            // Set the handRanking to have the same index of the player in $this->players
            $handRankings[$playerIndex] = $handRanking;

            // If current handRanking is the best hand, then set values to compare against future handRankings
            if ($handRanking->getHandValue() > $bestHandValue) {
                // The numerical value for the whole hand.
                // Used to determine actual best hand including kickers.
                $bestHandValue = $handRanking->getHandValue();
                // Hand type value are the significant cards when determining a hand (without kickers)
                // i.e. in a three of a kind, it's the numerical value for the hand type and the first three cards
                // i.e. in a two pair, it's the numerical value for the hand type and the first four cards
                // Used when a kicker plays between the same type of hand (AAKK7 vs AAKK8)
                // This is NOT used to determine the actual best hand, but to determine if we should include kickers
                // in the hand description or not.
                $bestHandTypeValue = $handRanking->getHandTypeValue();
            }
        }

        // Filter all handRankings where the getHandValue is not the best hand value
        $handRankings = array_filter($handRankings, function($handRanking) use ($bestHandValue, $bestHandTypeValue, $numberOfHandsDeterminedByKickers) {
            
            // Example, given three of the same two pairs [AAKKJ, AAKKJ, AAKK8]
            // they all have the same two pair value AAKK, but only the first two are the actual best hand
            // as the Jack kickers are better than the 8 kicker
            // Hand was determined by kicker value as all hands are the same two pair value
            // Set determinedByKickers and add to the number of hands determinedByKickers
            if ($handRanking->getHandTypeValue() === $bestHandTypeValue) {
                $handRanking->determinedByKickers = true;
                $numberOfHandsDeterminedByKickers++;
            }

            // Only return the actual best hands e.g. AAKKJ
            return $handRanking->getHandValue() === $bestHandValue;
        });

        // TODO:
        // TODO:
        // TODO:
        // TODO:
        // TODO:
        // TODO:
        // TODO:
        // Remove kicker information is hand was not determined by kickers in the end
        // Used when it's a chop between people with the same hand
        // Given three different three of a kinds, numberOfHandRankingsDeterminedByKickers = 3
        // [A, A, A, K, Q], [A, A, A, K, J], [A, A, A, K, Q]
        // Actual best hands count(handRankings) == 2, but three hands were determinedByKickers
        // So need to keep kicker information
        // If count(handRankings) == determinedByKickers then hand wasn't determined by kickers so 
        // remove kicker information
        // if count($handRankings) == $numberOfHandRankingsDeterminedByKickers
            // Loop through and setKickers to false
        
        // Return the $this->players which have the same keys as the winning handRankings
        // This will return multiple Players if there is a chopped pot
        return array_intersect_key($this->players, $handRankings);




        // // If the count of handRankings is greater than one, then there are at least two players with the same type of hand
        // // e.g. three straights
        // // Filter out handRankings according to kickers
        // if (count($handRankings) > 1) {

        //     // Default to true, but if bestHandRank doesn't have a case it will then get reset to false
        //     $setKickers = true;
            
        //     // These are the types of hands that could have kickers
        //     // e.g. in the same two pair hand, the kicker would be in the 5th card = index 4
        //     // e.g. in the same three of a kind, the kickers start from the 4th card = index 3
        //     // e.g. in the same high card, the kickers start from the 2nd card = index 1
        //     // e.g. in the same high card flush, the kickers start from the 2nd card = index 1
        //     switch($bestHandRank) {
        //         case HandRanking::HIGH_CARD_RANK:
        //             $kickerFromIndex = 1;
        //             break;
        //         case HandRanking::ONE_PAIR_RANK:
        //             $kickerFromIndex = 2;
        //             break;
        //         case HandRanking::TWO_PAIR_RANK:
        //             $kickerFromIndex = 4;
        //             break;
        //         case HandRanking::THREE_OF_A_KIND_RANK:
        //             $kickerFromIndex = 3;
        //             break;
        //         case HandRanking::FLUSH_RANK:
        //             $kickerFromIndex = 1;
        //             break;
        //         case HandRanking::FOUR_OF_A_KIND_RANK:
        //             $kickerFromIndex = 4;
        //             break;
        //         default:
        //             $setKickers = false;
        //     }

        //     // Get array of best hand card values like below
        //     // The indexes of kickers array are the indexes of $this->players
        //     // Player 0 => [K, Q, J, T, 9]
        //     // Player 2 => [Q, J, T, 9, 8]
        //     // Player 3 => [K, Q, J, T, 9]
        //     $kickers = array_map(fn($handRanking) => $handRanking->getKickers(), $handRankings);

        //     // Loop through all five cards of each best hand and filter out according to best value
        //     // at current index $kickerIndex
        //     // If at any point there is only one handRanking then we have a solo winner so stop looping
        //     for ($kickerIndex = 0; $kickerIndex < 5; $kickerIndex++) {

        //         // Get the values of each $kickers at index kickerIndex
        //         // e.g. first one will be [13, 12, 13] ([K, Q, K])
        //         $currentKickerValues = array_unique(array_column($kickers, $kickerIndex));
        //         // Get the max of [13, 12] = 13
        //         $bestCurrentKickerValue = max($currentKickerValues);

        //         // If this is a type of hand that could have kickers
        //         // And the kickerIndex is greater than or equal to the index of where kickers could start from
        //         // Only set kickers if there are multiple values in $currentKickerValues
        //         // to prevent exact same hands having kickers added on.
        //         if ($setKickers && ($kickerIndex >= $kickerFromIndex) && (count($currentKickerValues) > 1)) {
        //             // Set Kicker
        //             foreach($handRankings as $handRanking) {
        //                 $kickerCard = $handRanking->getHand()[$kickerIndex];
        //                 $handRanking->setKicker($kickerCard);
        //             }
        //             // NOTE:
        //             // May not need to setKickers = false because if we're applying a kicker then
        //             // the count of handRankings will now be 1 and will break further down.
        //             // $setKickers = false;
        //         }

        //         // Filter out handRankings where the card of best hand at index kickerIndex
        //         // is not the $bestCurrentKickerValue = 13
        //         $handRankings = array_filter($handRankings, function($handRank) use ($kickerIndex, $bestCurrentKickerValue) {
        //             return $handRank->getHand()[$kickerIndex]->getValueRank() === $bestCurrentKickerValue;
        //         });

        //         // If there is only one handRanking then we have a solo winner
        //         if (count($handRankings) === 1) {
        //             break;
        //         }
        //     }
        // }
    }
}