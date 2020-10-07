<?php

namespace SimpleHacker\PHPoker;

use Exception;

class Card
{
    /**
     * The card's value
     * 
     * @var mixed
     */
    protected $value;

    /**
     * The card's suit
     *
     * @var mixed
     */
    protected $suit;

    /**
     * An array of valid card values with ranking values
     *
     * @var array
     */
    protected $values = [
        1 => ['short_value' => 'a', 'value' => 'ace'],
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
    ];

    /**
     * An array of valid card suits with ranking values
     *
     * @var array
     */
    protected $suits = [
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
    public function __construct($value, $suit)
    {
        $this->setValue($value);
        $this->setSuit($suit);
    }

    /**
    * Set the card's value
    *
    * @param string|integer $value
    */
    private function setValue($value)
    {
        if (! $this->isValidValue($value)) {
            throw new Exception('Invalid card value');
        }

        if (is_int($value)) {
            $this->value = $value;
            return;
        }

        $value = strtolower($value);

        if (strlen($value) === 1) {
            $this->value = array_search($value, array_column($this->values, 'short_value')) + 1;
        } else {
            $this->value = array_search($value, array_column($this->values, 'value')) + 1;
        }
    }

    /**
    * Set the card's suit
    *
    * @param string|integer $suit
    */
    private function setSuit($suit)
    {
        if (! $this->isValidSuit($suit)) {
            throw new Exception('Invalid card suit');
        }
        
        if (is_int($suit)) {
            $this->suit = $suit;
            return;
        }
        
        $suit = strtolower($suit);

        // If suit is one character
        if (strlen($suit) === 1) {
            // Find index of short suit.
            $this->suit = array_search($suit, array_column($this->suits, 'short_suit')) + 1;
        } else {
            $suit = rtrim($suit, 's');
            $this->suit = array_search($suit, array_column($this->suits, 'suit')) + 1;
        }
    }

    /**
    * Check if valid value of card when instantiating
    * 
    * @param mixed $value
    * @return boolean
    */
    private function isValidValue($value): Bool
    {
        if (is_int($value)) {
            return ($value > 0 && $value < 14);
        }

        $value = strtolower($value);

        if (strlen($value) === 1) {
            return array_search($value, array_column($this->values, 'short_value')) + 1;
        } else {
            return array_search($value, array_column($this->values, 'value')) + 1;
        }
    }

    /**
    * Check if valid suit of card when instantiating
    * 
    * @param mixed $suit
    * @return boolean
    */
    private function isValidSuit($suit): Bool
    {
        if (is_int($suit)) {
            return ($suit > 0 && $suit < 14);
        }

        $suit = strtolower($suit);

        // Valid suits
        if (strlen($suit) === 1) {
            return in_array($suit, array_column($this->suits, 'short_suit'));
        } else {
            $suit = rtrim($suit, 's');
            return in_array($suit, array_column($this->suits, 'suit'));
        }
    }

    /**
    * Returns the card's value
    * 
    * @return string
    */
    public function getValue(): String
    {
        return ucwords($this->values[$this->value]['value']);
    }

    /**
    * Returns the card's value
    * 
    * @return string
    */
    public function getShortValue(): String
    {
        return ucwords($this->values[$this->value]['short_value']);
    }

    /**
    * Returns the card's value
    * 
    * @return string
    */
    public function getValueRank(): String
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
        return ucwords($this->suits[$this->suit]['suit']) . 's';
    }

    /**
    * Returns the card's short suit
    * 
    * @return string
    */
    public function getShortSuit(): String
    {
        return $this->suits[$this->suit]['short_suit'];
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
}