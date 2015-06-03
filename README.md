# MaResidence API Client Library for PHP #

## Description ##
The MR API Client Library enables you to work with ma-residence API on your server.

## Beta ##
This library is in Beta. We're comfortable enough with the stability and features of the library that we want you to build real production applications on it. We will make an effort to support the public and protected surface of the library and maintain backwards compatibility in the future. While we are still in Beta, we reserve the right to make incompatible changes. If we do remove some functionality (typically because better functionality exists or if the feature proved infeasible), our intention is to deprecate and provide sample time for developers to update their code.

## Requirements ##
See `composer.json` file.

## Basic Example ##

    use Symfony\Component\HttpFoundation\Session\Session;
    use MaResidence\Component\ApiClient\Client;

    $options = [
        'client_id' => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'username' => USERNAME,
        'password' => PASSWORD,
        'endpoint' => 'https://www.ma-residence.fr/api/'
        'token_url' => 'https://www.ma-residence.fr/oauth/v2/token'
    ];
    
    $client = new Client($options);

    if (false === $client->isAuthenticated()) {
        $client->authenticate();
    }

    $client->getNews();
