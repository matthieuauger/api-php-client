<?php

namespace tests\MaResidence\Component\ApiClient;

use MaResidence\Component\ApiClient\Client;
use Phake;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testCanGetNews()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testIsAccessTokenExpired()
    {
        $tokenStorage = Phake::mock('MaResidence\Component\ApiClient\TokenStorageInterface');
        Phake::when($tokenStorage)->get("mr_api_client.oauth_token")->thenReturn("WTF");

        $client = new Client($tokenStorage, '', '', '', '', '', '');
        $this->assertTrue($client->isAccessTokenExpired());
    }
}
