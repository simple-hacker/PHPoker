# PHPoker
PHP library package for No Limit Texas Holdem poker.  This package allows you to find the best Texas Holdem hand out of the given Cards.

## Installation
Require the package using composer

```bash
composer require simple-hacker/phpoker
```


## Usage

```php
use simplehacker\PHPoker\Hands\TexasHoldemHand;

$communityCards = '3hJdQdAdKd';
$holeCards = 'Td6s';

$hand = new TexasHoldemHand($communityCards, $holeCards);
```

The Cards as a string of short values, or an array of short values and/or Card instances.

The following results at the same:

```php
$communityCards = '3hJdQdAdKd';
$communityCards = ['3h', 'Jd', 'Qd', 'Ad', 'Kd'];
$communityCards = [new Card('3h'), new Card('Jd'), new Card('Qd'), new Card('Ad'), new Card('Kd')];
$communityCards = [new Card('3h'), 'Jd', new Card('Qd'), 'Ad', new Card('Kd')];  // A mix of both
```

Once a hand has been instantiated, you are able to get the hand description as well as a numerical value of the hand used for comparing against other hands.

```php
use simplehacker\PHPoker\Hands\TexasHoldemHand;

$communityCards = '3hJdQdAdKd';
$holeCards = 'Td6s';

$hand = new TexasHoldemHand($communityCards, $holeCards);

// Royal Flush, Ace to Ten of Diamonds
$hand->getDescription();

// AdKdQdJdTd
$hand->getShortDescription();

// 10
$hand->getHandRank();

// const ROYAL_FLUSH_RANK      = 10;
// const STRAIGHT_FLUSH_RANK   = 9;
// const FOUR_OF_A_KIND_RANK   = 8;
// const FULL_HOUSE_RANK       = 7;
// const FLUSH_RANK            = 6;
// const STRAIGHT_RANK         = 5;
// const THREE_OF_A_KIND_RANK  = 4;
// const TWO_PAIR_RANK         = 3;
// const ONE_PAIR_RANK         = 2;
// const HIGH_CARD_RANK        = 1;

// 11459770
$hand->getHandValue();

// Hand value is generated from converting Hand Rank and all Card values to a binary string, and converting back to base 10.  This ensures the best hand will always be the highest number
```


## Cards
Cards can be instantiated in numerous ways.  The following all result in the Ace of Spades:

```php
// As a short value where the first character is card value 23456789TJQKA,
// and the second character is card suit as shcd
new Card('As');

// Arguments for both value and suit
new Card('Ace', 'Spades');
new Card('A', 's');

// As numerical values
// Jack = 11, Queen = 12, King = 13, Ace = 1 or 14
// Clubs = 1, Diamonds = 2, Hearts = 3, Spades = 4
new Card(14, 4);

// Or any combination of the above
new Card('Ace', 's');
new Card(1, 'spades');
```
A InvalidCardException will be thrown for invalid values or suits

## Upcoming releases
- Compare TexasHoldemHands to determine winner
- Other poker variants such as Omaha, Short Deck, Razz, Stud etc

## Disclaimer
I am not responsible for any money won or loss as a result of any mistakes made in this package.  This was created as a learning exercise so use this package at your own risk.  

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](./LICENSE.md)