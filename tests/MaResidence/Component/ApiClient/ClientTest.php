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
        //$this->markTestIncomplete('This test has not been implemented yet.');

        $session = Phake::mock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        Phake::when($session)->get("mr_api_client.oauth_token")->thenReturn("WTF");

        $client = new Client($session, '', '', '', '', '', '', "mr_api_client.oauth_token");
        $this->assertTrue($client->isAccessTokenExpired());

    }
}
