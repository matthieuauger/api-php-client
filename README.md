[![Build Status](https://travis-ci.org/google/google-api-php-client.svg)](https://travis-ci.org/google/google-api-php-client)

# MaResidence API Client Library for PHP #

## Description ##
The MR API Client Library enables you to work with ma-residence API on your server.

## Beta ##
This library is in Beta. We're comfortable enough with the stability and features of the library that we want you to build real production applications on it. We will make an effort to support the public and protected surface of the library and maintain backwards compatibility in the future. While we are still in Beta, we reserve the right to make incompatible changes. If we do remove some functionality (typically because better functionality exists or if the feature proved infeasible), our intention is to deprecate and provide sample time for developers to update their code.

## Requirements ##
* [PHP 5.5.1 or higher](http://www.php.net/)
* [PHP JSON extension](http://php.net/manual/en/book.json.php)

*Note*: some features (service accounts and id token verification) require PHP 5.5.0 and above due to cryptographic algorithm requirements. 

## Developer Documentation ##
http://developers.ma-residence.fr/api-client-library/php

## Installation ##

For the latest installation and setup instructions, see [the documentation](https://developers.ma-residence.fr/api-client-library/php/start/installation).

## Basic Example ##
See the examples/ directory for examples of the key client features.
```PHP
<?php
  use Symfony\Component\HttpFoundation\Session\SessionInterface;
  
  $client = new MaResidenceClient(SessionInterface $session, $clientId, $clientSecret, $username, $password);
  $client->getNews();
  
```

## Frequently Asked Questions ##

### What do I do if something isn't working? ###

For support with the library the best place to ask is via the  ma-residence-api-php-client tag on StackOverflow: http://stackoverflow.com/questions/tagged/ma-residence-api-php-client

If there is a specific bug with the library, please file a issue in the Github issues tracker, including a (minimal) example of the failing code and any specific errors retrieved. Feature requests can also be filed, as long as they are core library requests, and not-API specific: for those, refer to the documentation for the individual APIs for the best place to file requests. Please try to provide a clear statement of the problem that the feature would address.

## Code Quality ##

The PHP Coding Standards Fixer tool fixes most issues in your code when you want to follow the PHP coding standards as defined in the PSR-1 and PSR-2 documents.
If you are already using PHP_CodeSniffer to identify coding standards problems in your code, you know that fixing them by hand is tedious, especially on large projects. This tool does the job for you.

[PHP Coding Standards Fixer](http://cs.sensiolabs.org/)

        php php-cs-fixer.phar fix /path/to/code --dry-run
