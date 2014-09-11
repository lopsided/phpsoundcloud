# alcohol\phpsoundcloud

A client written in PHP for SoundCloud's API.

[![Latest Stable Version](https://poser.pugx.org/alcohol/phpsoundcloud/v/stable.png)](https://packagist.org/packages/alcohol/phpsoundcloud)
[![Build Status](https://travis-ci.org/alcohol/phpsoundcloud.png?branch=master)](https://travis-ci.org/alcohol/phpsoundcloud)
[![License](https://poser.pugx.org/alcohol/phpsoundcloud/license.png)](https://packagist.org/packages/alcohol/phpsoundcloud)

## Installation

Either install directly from command line using composer:

``` sh
$ composer require "alcohol/phpsoundcloud:~1.0"
```

or manually include it as a dependency in your composer.json:

``` javascript
{
    "require": {
        "alcohol/phpsoundcloud": "~1.0"
    }
}
```

## Usage

``` php
<?php

use Alcohol\SoundCloud;

$soundcloud = new SoundCloud($clientId, $clientSecret, $redirectUri);

// do stuff with it - see class for functionality, no docs yet
```

## Contributing

Feel free to submit a pull request or create an issue.
