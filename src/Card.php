<?php

namespace simplehacker\PHPoker;

use simplehacker\PHPoker\Exceptions\InvalidCardException;

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
        if (is_int($value) && $value > 0 && $value < 14) {
            $this->value = $value;
        }

        // Otherwise a single character or string was passed.
        // e.g. K or King
        if (is_string($value)) {
            $modifiedValue = strtolower($value);
            
            if (strlen($modifiedValue) === 1) {
                $index = array_search($modifiedValue, array_column($this->values, 'short_value'));
                $this->value = ($index !== false) ? $index + 1 : null;
            } else {
                $index = array_search($modifiedValue, array_column($this->values, 'value'));
                $this->value = ($index !== false) ? $index + 1 : null;
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
                $index = array_search($modifiedSuit, array_column($this->suits, 'short_suit'));
                $this->suit = ($index !== false) ? $index + 1 : null;
            } else {
                $modifiedSuit = substr($modifiedSuit, -1) === 's' ? substr($modifiedSuit, 0, -1) : $modifiedSuit;
                $index = array_search($modifiedSuit, array_column($this->suits, 'suit'));
                $this->suit = ($index !== false) ? $index + 1 : null;
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