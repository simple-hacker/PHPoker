<?php

namespace simplehacker\PHPoker\Games;

use simplehacker\PHPoker\Deck;
use simplehacker\PHPoker\Hand;
use simplehacker\PHPoker\Player;
use simplehacker\PHPoker\Exceptions\HandException;
use simplehacker\PHPoker\Exceptions\InvalidActionException;
use simplehacker\PHPoker\HighHandEvaluator;

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
    * Showdown
    * 
    * @return void
    */
    public function showdown()
    {
        // TODO: Check all betting has ceased
        if (count($this->communityCards) !== 5) {
            throw new InvalidActionException('Unable to showdown until all cards have been dealt and betting has stopped');
        }

        $this->getWinners();
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

        $hands = [];
        $bestHandValue = 0;
        $besthandValueWithoutKickers = 0;

        // Loop through all players and combine communityCard with player's holeCards (allCards)
        // Get the best hand for each player using allCards
        // Get the hand ranking value of each handRank, setting the best hand rank value along the way
        foreach ($this->players as $playerIndex => $player) {

            // NOTE: When dealing with pots, check to see if player has already built up their hand ranking to save calculating again
            // if ($player->getHandRanking()) {
            //      $hand = $player->getHandRanking();
            // } else {
            //
                $allCards = [...$this->communityCards, ...$player->getHoleCards()];
                $hand = new HighHandEvaluator($allCards);
                $player->setHand($hand);
            // }
            
            // Group the handRankings together first by the same handValueWithoutKickers and then by actual handValue
            // The keys of each HandRank object will be the key of Player in $this->players
            // Note: The keys will be the cards converted to binary then to decimal.
            // Four players => [AAKK8, AA88K, AAKK7, AAKK8]
            // e.g. [
            //      'AAKK' => [
            //          'AAKK8' => [1 => Player 1 HandRank obj, 4 => Player 4 HandRank obj],
            //          'AAKK7' => [3 => Player 3 HandRank obj]
            //      ],
            //      'AA88', => [
            //          'AA88K' => [2 => Player 2 HandRank obj]
            //      ]
            // ]
            // Set the handRanking to have the same index of the player in $this->players
            $handValueWithoutKickers = $hand->getHandValueWithoutKickers();
            $handValue = $hand->getHandValue();
            $hands[$handValueWithoutKickers][$handValue][$playerIndex] = $hand;

            // If current handRanking is the best hand, then set values to compare against future handRankings
            if ($handValue > $bestHandValue) {
                // The numerical value for the whole hand.
                // Used to determine actual best hand including kickers.
                $bestHandValue = $handValue;
                // Hand type value are the significant cards when determining a hand (without kickers)
                // i.e. in a three of a kind, it's the numerical value for the hand type and the first three cards
                // i.e. in a two pair, it's the numerical value for the hand type and the first four cards
                // Used when a kicker plays between the same type of hand (AAKK7 vs AAKK8)
                // This is NOT used to determine the actual best hand, but to determine if we should include kickers
                // in the hand description or not.
                $besthandValueWithoutKickers = $handValueWithoutKickers;
            }
        }
        
        // Loop through all handRankings handTypes.
        // If the count of handType array is greater than one then that particular hand ranking was determined by it's kickers,
        // so set determinedByKickers to true.
        // In the above example, AAKK was determined by kickers 8 and 7 (but not AA88)
        foreach ($hands as $handTypes) {
            if (count($handTypes) > 1) {
                foreach ($handTypes as $hand) {
                    foreach ($hand as $hand) {
                        $hand->determinedByKickers = true;
                    }
                }
            }
        }

        // Return the winners
        // We know the besthandValueWithoutKickers and bestHandValue so we can just return the Players than have been
        // put in to the actual best hand bucket
        // Return the Player objects where the keys intersect with the keys of the winning bucket.
        return array_intersect_key($this->players, $hands[$besthandValueWithoutKickers][$bestHandValue]);
    }
}