<?php

namespace simplehacker\PHPoker;

use simplehacker\PHPoker\Exceptions\InvalidCardException;

class Card
{
    /**
     * The card's value
     * 
     * @var integer
     */
    protected $value;

    /**
     * The card's suit
     *
     * @var integer
     */
    protected $suit;

    /**
     * An array of valid card values with ranking values
     *
     * @var array
     */
    public static $values = [
        2 => ['short_value' => 2, 'value' => 'two'],
        3 => ['short_value' => 3, 'value' => 'three'],
        4 => ['short_value' => 4, 'value' => 'four'],
        5 => ['short_value' => 5, 'value' => 'five'],
        6 => ['short_value' => 6, 'value' => 'six'],
        7 => ['short_value' => 7, 'value' => 'seven'],
        8 => ['short_value' => 8, 'value' => 'eight'],
        9 => ['short_value' => 9, 'value' => 'nine'],
        10 => ['short_value' => 't', 'value' => 'ten'],
        11 => ['short_value' => 'j', 'value' => 'jack'],
        12 => ['short_value' => 'q', 'value' => 'queen'],
        13 => ['short_value' => 'k', 'value' => 'king'],
        14 => ['short_value' => 'a', 'value' => 'ace'],
    ];

    /**
     * An array of valid card suits with ranking values
     *
     * @var array
     */
    public static $suits = [
        1 => ['short_suit' => 'c', 'suit' => 'club'],
        2 => ['short_suit' => 'd', 'suit' => 'diamond'],
        3 => ['short_suit' => 'h', 'suit' => 'heart'],
        4 => ['short_suit' => 's', 'suit' => 'spade'],
    ];

    /**
     * Instantiate card with value and suit
     *
     * @param string|integer $value
     * @param string|integer $suit
     */
    public function __construct($value, $suit = null)
    {
        // If suit is not given, then send shortValue for validation
        // and split into value and suit
        if (! $suit) {
            [$value, $suit] = $this->splitValue($value);
        }

        $this->setValue($value);
        $this->setSuit($suit);
    }

    /**
     * Return an array of Card instances given an array or a string
     * Array can contain mixed instances of Card or a short Card description string
     * e.g. [new Card('Ah'), '3s', '4c', new Card('9s')]
     * or
     * e.g. 'Ah3s4c'
     *
     * @param array|string $cards
     */
    public static function convertToCards($cards)
    {
        if (is_array($cards))
        {
            // Map over array.
            // If it's already an instance of Card then return the Card instance
            // Else try to instantiate a Card with the value
            return array_map(fn($card) => ($card instanceof Card) ? $card : new Card($card), $cards);
        }

        if (is_string($cards))
        {
            // Split string in to every two characters
            $shortCards = str_split($cards, 2);
            // Try to instantiate a Card instance with every two characters
            return array_map(fn($shortCardValue) => new Card($shortCardValue), $shortCards);
        }

        // If it's not an array or string throw exception
        throw new InvalidCardException('Invalid argument type for Cards');
    }

    /**
    * When Card class is converted to string, return the shortValue string
    * This is used when compairing two Cards as a string when using array_unique
    * 
    * @return string
    */
    public function __toString(): String
    {
        return $this->getShortDescription();
    }

    /**
    * If short value is given, split it in to value and suit
    * 
    * @param string $value
    * @return array 
    */
    public function splitValue($shortValue): Array
    {
        $pattern = '/[A2-9TJQK][cdhs]/';
        if (!is_string($shortValue) || strlen($shortValue) !== 2 || !preg_match($pattern, $shortValue)) {
            throw new InvalidCardException("Invalid short value ($shortValue)");
        }
        
        return [$shortValue[0], $shortValue[1]];
    }

    /**
    * Validate and set the card's value
    *
    * @param string|integer $value
    */
    private function setValue($value)
    {
        if (is_int($value) && $value > 0 && $value < 15) {
            $this->value = ($value === 1) ? 14 : $value;
        }

        // Otherwise a single character or string was passed.
        // e.g. K or King
        if (is_string($value)) {
            $modifiedValue = strtolower($value);
            
            if (strlen($modifiedValue) === 1) {
                // Need to combine the column and array keys so we have the correct index
                $this->value = array_search($modifiedValue, array_combine(array_keys(self::$values), array_column(self::$values, 'short_value')));
            } else {
                // Need to combine the column and array keys so we have the correct index
                $this->value = array_search($modifiedValue, array_combine(array_keys(self::$values), array_column(self::$values, 'value')));
            }
        }
        
        // If card's value is empty at this point then invalid card value given.
        if (empty($this->value)) {
            throw new InvalidCardException("Invalid card value ($value)");
        }
    }

    /**
    * Validate and set the card's suit
    *
    * @param string|integer $suit
    */
    private function setSuit($suit)
    {
        if (is_int($suit) && $suit > 0 && $suit < 5) {
            $this->suit = $suit;
        }

        // Otherwise a single character or string was passed.
        // e.g. H or Hearts
        if (is_string($suit)) {

            $modifiedSuit = strtolower($suit);
            
            if (strlen($modifiedSuit) === 1) {
                $this->suit = array_search($modifiedSuit, array_combine(array_keys(self::$suits), array_column(self::$suits, 'short_suit')));
            } else {
                $modifiedSuit = substr($modifiedSuit, -1) === 's' ? substr($modifiedSuit, 0, -1) : $modifiedSuit;
                $this->suit = array_search($modifiedSuit, array_combine(array_keys(self::$suits), array_column(self::$suits, 'suit')));
            }
        }

        // If card's suit is empty at this point then invalid card suit given.
        if (empty($this->suit)) {
            throw new InvalidCardException("Invalid card suit ($suit)");
        }
    }

    /**
    * Returns the card's value
    * 
    * @return string
    */
    public function getValue(): String
    {
        $value = ($this->value === 1) ? 14 : $this->value;
        return ucwords(self::$values[$value]['value']);
    }

    /**
    * Returns the card's value
    * 
    * @return string
    */
    public function getShortValue(): String
    {
        $value = ($this->value === 1) ? 14 : $this->value;
        return ucwords(self::$values[$value]['short_value']);
    }

    /**
    * Returns the card's value
    * 
    * @return int
    */
    public function getValueRank(): Int
    {
        return $this->value;
    }

    /**
    * Returns the card's suit
    * 
    * @return string
    */
    public function getSuit(): String
    {
        return ucwords(self::$suits[$this->suit]['suit']) . 's';
    }

    /**
    * Returns the card's short suit
    * 
    * @return string
    */
    public function getShortSuit(): String
    {
        return self::$suits[$this->suit]['short_suit'];
    }

    /**
    * Returns the card's suit rank value
    * 
    * @return integer
    */
    public function getSuitRank(): Int
    {
        return $this->suit;
    }

    /**
    * Returns the card's full description
    * e.g. Four of Hearts, King of Spades
    * 
    * @return string
    */
    public function getDescription(): String
    {
        return $this->getValue() . ' of ' . $this->getSuit();
    }

    /**
    * Returns the card's short description
    * e.g. 4h, Kc, 3s
    * 
    * @return string
    */
    public function getShortDescription(): String
    {
        return $this->getShortValue() . $this->getShortSuit();
    }

    /**
    * Flips the value of Ace to low value
    * 
    * @param 
    * @return void
    */
    public function flipAce(): Void
    {
        if ($this->value !== 1 && $this->value !== 14) {
            throw new InvalidCardException('Unable to flip a non ace Card');
        }

        $this->value = ($this->value === 14) ? 1 : 14;
    }
}