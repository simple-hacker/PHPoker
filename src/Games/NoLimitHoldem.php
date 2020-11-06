<?php

namespace simplehacker\PHPoker\Games;

use simplehacker\PHPoker\Deck;
use simplehacker\PHPoker\Player;
use simplehacker\PHPoker\Exceptions\HandException;
use simplehacker\PHPoker\Exceptions\InvalidActionException;

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
    private $deck;

    /**
     * The community cards of the hand
     * 
     * @var array
     */
    private $communityCards = [];

    /**
     * Boolean if the hand has already been dealt to players
     * 
     * @var bool
     */
    private $dealt = false;

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
    private function giveCardsToPlayer(Player $player, int $numberOfCards = 1)
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
    private function addCommunityCards(int $numberOfCards = 1)
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
}