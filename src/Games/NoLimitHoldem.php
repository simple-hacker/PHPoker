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
        // Cannot get winners if the river has not been dealt
        // TODO: Also check that all betting ceases (when working with pots)
        if (count($this->communityCards) !== 5) {
            throw new InvalidActionException('Cannot get winners until there are five community cards');
        }

        $handRankings = [];
        $bestHandRank = 0;

        // Loop through all players and combine communityCard with player's holeCards (allCards)
        // Get the best hand for each player using allCards
        // Get the hand ranking value of each handRank, setting the best hand rank value along the way
        foreach ($this->players as $playerIndex => $player) {
            $allCards = [...$this->communityCards, ...$player->getHoleCards()];
            $handRank = new HandRanking($allCards);            
            if ($handRank->getRank() > $bestHandRank) {
                $bestHandRank = $handRank->getRank();
            }
            // Set the handRank to have the same index of the player in $this->players
            $handRankings[$playerIndex] = $handRank;
            $player->setHandRanking($handRank);
        }

        // Filter out any handRankings that does not have the same rank value as bestHandRank
        // e.g. Given three players
        // Player 0 => Straight [K, Q, J, T, 9] (Rank 5)
        // Player 1 => Two pair [K, K, Q, Q, 4] (Rank 3)
        // Player 2 => Straight [Q, J, T, 9, 8] (Rank 5)
        // Player 3 => Straight [K, Q, J, T, 9] (Rank 5)
        // Then bestHandRank = 5
        // Filter all handRankings where the getRank is not 5, so handRankings will only include the straights
        $handRankings = array_filter($handRankings, function($handRank) use ($bestHandRank) {
            return $handRank->getRank() === $bestHandRank;
        });

        // If the count of handRankings is greater than one, then there are at least two players with the same type of hand
        // e.g. three straights
        // Filter out handRankings according to kickers
        if (count($handRankings) > 1) {

            // Default to true, but if bestHandRank doesn't have a case it will then get reset to false
            $setKickers = true;
            
            // These are the types of hands that could have kickers
            // e.g. in the same two pair hand, the kicker would be in the 5th card.
            // e.g. in the same three of a kind, the kickers start from the 4th card
            // e.g. in the same high card, the kickers start from the 2nd card
            // e.g. in the same high card flush, the kickers start from the 2nd card
            switch($bestHandRank) {
                case 1:
                    // High Card
                    $kickerFromValue = 2;
                    break;
                case 2:
                    // One pair
                    $kickerFromValue = 3;
                    break;
                case 3:
                    // Two pair
                    $kickerFromValue = 5;
                    break;
                case 4:
                    // Three of a kind
                    $kickerFromValue = 4;
                    break;
                case 6:
                    // Flush
                    $kickerFromValue = 2;
                    break;
                case 8:
                    // Four of a kind
                    $kickerFromValue = 5;
                    break;
                default:
                    $setKickers = false;
            }

            // Get array of best hand card values like below
            // The indexes of kickers array are the indexes of $this->players
            // Player 0 => [K, Q, J, T, 9]
            // Player 2 => [Q, J, T, 9, 8]
            // Player 3 => [K, Q, J, T, 9]
            $kickers = array_map(fn($handRanking) => $handRanking->getKickers(), $handRankings);

            // Loop through all five cards of each best hand and filter out according to best value
            // at current index $kickerIndex
            // If at any point there is only one handRanking then we have a solo winner so stop looping
            for ($kickerIndex = 1; $kickerIndex < 6; $kickerIndex++) {

                // Get the values of each $kickers at index kickerIndex
                // e.g. first one will be [13, 12, 13] ([K, Q, K])
                $currentKickerValues = array_unique(array_column($kickers, $kickerIndex - 1));
                // Get the max of [13, 12] = 13
                $bestCurrentKickerValue = max($currentKickerValues);

                // If this is a type of hand that could have kickers
                // And the kickerIndex is greater than or equal to the index of where kickers could start from
                // Only set kickers if there are multiple values in $currentKickerValues
                // to prevent exact same hands having kickers added on.
                if ($setKickers && ($kickerIndex >= $kickerFromValue) && (count($currentKickerValues) > 1)) {
                    // Set Kicker
                    foreach($handRankings as $handRanking) {
                        $kickerCard = $handRanking->getHand()[$kickerIndex - 1];
                        $handRanking->setKicker($kickerCard);
                    }
                    // NOTE:
                    // May not need to setKickers = false because if we're applying a kicker then
                    // the count of handRankings will now be 1 and will break further down.
                    // $setKickers = false;
                }

                // Filter out handRankings where the card of best hand at index kickerIndex
                // is not the $bestCurrentKickerValue = 13
                $handRankings = array_filter($handRankings, function($handRank) use ($kickerIndex, $bestCurrentKickerValue) {
                    return $handRank->getHand()[$kickerIndex - 1]->getValueRank() === $bestCurrentKickerValue;
                });

                // If there is only one handRanking then we have a solo winner
                if (count($handRankings) === 1) {
                    break;
                }
            }
        }

        // handRankings now only contains the best possible handRanking, including finding kickers if needed
        // handRanking keys are index of $this->players to which the handRanking belongs to
        // Return the intersection of $this->players which have a key belonging to keys in handRankings
        // This will return multiple Players if there is a chopped pot

        // In the above example, Player 0 and Player 3 have the same exact hand KQJT9
        // handRanking keys will be [0, 3]
        // Return the $this->players which have the keys 0 and 3, these are the winners
        return array_intersect_key($this->players, $handRankings);
    }
}