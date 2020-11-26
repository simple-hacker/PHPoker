# PHPoker
PHP library package for No Limit Texas Holdem poker.  This package allows you to find the best Texas Holdem hand out of the given Cards.

## Installation
Require the package using composer

```bash
composer require simple-hacker/PHPoker
```

## Usage

```php
use simple-hacker\PHPoker\Hands\TexasHoldemHand

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
## Cards



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