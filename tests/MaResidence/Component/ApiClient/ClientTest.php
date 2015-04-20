<?php

namespace tests\MaResidence\Component\ApiClient;

use GuzzleHttp\Event\Emitter;
use GuzzleHttp\Stream\Stream;
use MaResidence\Component\ApiClient\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\ResponseInterface;
use Doctrine\Common\Cache\Cache;
use GuzzleHttp\Event\EmitterInterface;
use GuzzleHttp\Client AS GuzzleClient;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use MaResidence\Component\ApiClient\Storage\InMemoryStorage;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testGetClient()
    {
        $client = new Client($this->getClientOptions());

        $this->assertInstanceOf('MaResidence\Component\ApiClient\Client', $client);
        $this->assertEquals('Q0wM4+5sxBszl', $client->getClientId());
        $this->assertFalse($client->isAuthenticated());
    }

    public function testAuthenticate()
    {
        $token = ['access_token' => 'DxhqhXaXIRLad', 'expires_in' => 30];
        $responseMessage = $this->getMockBuilder('GuzzleHttp\Message\ResponseInterface')->getMock();
        $responseMessage->expects($this->once())->method('getStatusCode')->will($this->returnValue(200));
        $responseMessage->expects($this->once())->method('json')->will($this->returnValue($token));

        $emitter = $this->getMockBuilder('GuzzleHttp\Event\EmitterInterface')->getMock();

        $httpClient = $this->getMockBuilder('GuzzleHttp\ClientInterface')->getMock();
        $httpClient->expects($this->once())->method('get')->with($this->equalTo('http://path/to/token/endpoint'))->will($this->returnValue($responseMessage));
        $httpClient->expects($this->once())->method('getEmitter')->will($this->returnValue($emitter));

        $client = new Client($this->getClientOptions(), $httpClient);
        $client->authenticate();

        $this->assertTrue($client->isAuthenticated());
    }

    public function testCacheIsSet()
    {
        $httpClient = new GuzzleClient();
        $mock = new Mock([new Response(200, [], new Stream(fopen('data://text/plain,' . '{"toto":"titi"}','r')))]);

        $httpClient->getEmitter()->attach($mock);

        // save method should be call once
        $cacheDriver =  $this->getMockBuilder('Doctrine\Common\Cache\Cache')->getMock();
        $cacheDriver->expects($this->any())->method('save');
        $cacheDriver->expects($this->once())->method('fetch');

        $options = $this->getClientOptions();

        $options['cache_driver'] = $cacheDriver;

        $tokenStorage = new InMemoryStorage();
        $tokenStorage->setAccessToken(['access_token' => 'DxhqhXaXIRLad', 'expires_in' => 30, 'created_at' => time()]);

        $client = new Client($options, $httpClient, $tokenStorage);

        $client->getAdverts();
    }

    private function getClientOptions()
    {
        return [
            'client_id' => 'Q0wM4+5sxBszl',
            'client_secret' => 'MvKMvwqyYTOo',
            'username' => 'n9cpnUG2C',
            'password' => '1myLGWeUSa',
            'endpoint' => 'http://path/to/api/endpoint',
            'token_url' => 'http://path/to/token/endpoint'
        ];
    }
}
