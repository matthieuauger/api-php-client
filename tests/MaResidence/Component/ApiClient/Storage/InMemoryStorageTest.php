<?php

namespace tests\MaResidence\Component\ApiClient;


use MaResidence\Component\ApiClient\Storage\InMemoryStorage;

class InMemoryStorageTest extends \PHPUnit_Framework_TestCase
{

    public function testGettersAndSetters()
    {
        $storage = new InMemoryStorage();

        $token = ['access_token' => 'K0TMAJKqZg6'];

        $storage->setAccessToken($token);
        $this->assertSame($token, $storage->getAccessToken());
    }

    public function testTokenIsExpired()
    {
        $storage = new InMemoryStorage();
        $missingTimePropertyToken = ['access_token' => 'K0TMAJKqZg6'];
        $storage->setAccessToken($missingTimePropertyToken);
        $this->assertTrue($storage->isAccessTokenExpired());
        $validToken = ['access_token' => 'K0TMAJKqZg6', 'created_at' => time(), 'expires_in' => 30];
        $storage->setAccessToken($validToken);
        $this->assertFalse($storage->isAccessTokenExpired());
        $invalidToken = $validToken = ['access_token' => 'K0TMAJKqZg6', 'created_at' => time() - 35, 'expires_in' =>  30];
        $storage->setAccessToken($invalidToken);
        $this->assertTrue($storage->isAccessTokenExpired());
    }
}
