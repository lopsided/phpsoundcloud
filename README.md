# Alcohol\SoundCloud

A client written in PHP for SoundCloud's API.

## Installing

Either install directly from command line using composer:

``` sh
$ composer require "alcohol/phpsoundcloud:~2.0"
```

or manually include it as a dependency in your composer.json:

``` javascript
{
    "require": {
        "alcohol/phpsoundcloud": "~2.0"
    }
}
```

## Using

``` php
<?php

use Alcohol\SoundCloud;

$options = [
    'client_id' => 'yourId',
    'secret' => 'yourSecret',
    'redirect_uri' => 'http://domain.tld/redirect'
];

$soundcloud = new SoundCloud($options);

// do stuff with it - see class for functionality, no docs yet
```

## Contributing

Feel free to submit a pull request or create an issue.

## License

Alcohol\SoundCloud is licensed under the MIT license.
