<?php

namespace MaResidence\Component\ApiClient;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use MaResidence\Component\ApiClient\Exception\BadRequestException;
use MaResidence\Component\ApiClient\Exception\InvalidClientException;
use MaResidence\Component\ApiClient\Exception\UnauthorizedClientException;

class Client
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var GuzzleClient
     */
    private $client;

    /**
     * @var string Your ClientId provided by ma-residence.fr
     */
    private $clientId;

    /**
     * @var string Your ClientSecret provided by ma-residence.fr
     */
    private $clientSecret;

    /**
     * @var string Your ursername provided by ma-residence.fr
     */
    private $username;

    /**
     * @var string Your password provided by ma-residence.fr
     */
    private $password;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $token_url;

    /**
     * @var string
     */
    private $session_token_key;

    /**
     * @param SessionInterface $session
     * @param string           $clientId
     * @param string           $clientSecret
     * @param string           $username
     * @param string           $password
     * @param string           $endpoint
     * @param string           $token_url
     * @param string           $session_token_key
     */
    public function __construct(SessionInterface $session, $clientId, $clientSecret, $username, $password, $endpoint, $token_url, $session_token_key = 'mr_api_client.oauth_token')
    {
        $this->session = $session;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->username = $username;
        $this->password = $password;
        $this->endpoint = $endpoint;
        $this->token_url = $token_url;
        $this->session_token_key = $session_token_key;
        $this->client = new GuzzleClient(['base_url' => $endpoint]);
    }

    /**
     *
     */
    public function authenticate()
    {
        $token = $this->getToken();
        $token['created_at'] = time();

        $this->setToken($token);

    }

    public function setToken($token)
    {
        $this->session->set($this->session_token_key, $token);
    }

    /**
     * @return array|bool|float|int|string
     * @throws \LogicException
     */
    public function getToken()
    {
        $options = [
            'query' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'password',
                'username' => $this->username,
                'password' => $this->password,
            ]
        ];

        try {
            $response = $this->client->get($this->token_url, $options);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $body = $response->json();

            if (array_key_exists('error', $body) && $body['error'] == 'invalid_client') {
                $message = array_key_exists('error_description', $body) ? $body['error_description'] : 'Error description not available';

                throw new InvalidClientException($message, $response->getReasonPhrase(), $response->getStatusCode(), $response->getEffectiveUrl(), $body);
            }

            if (array_key_exists('error', $body) && $body['error'] == 'unauthorized_client') {
                $message = array_key_exists('error_description', $body) ? $body['error_description'] : 'Error description not available';

                throw new UnauthorizedClientException($message, $response->getReasonPhrase(), $response->getStatusCode(), $response->getEffectiveUrl(), $body);
            }

            throw new BadRequestException($e->getMessage(), $response->getReasonPhrase(), $response->getStatusCode(), $response->getEffectiveUrl(), $body);
        }

        if (200 !== $response->getStatusCode()) {
            throw new BadRequestException('An error occurred when trying to GET token data from MR API', $response->getReasonPhrase(), $response->getStatusCode(), $response->getEffectiveUrl());
        }

        return $response->json();
    }

    /**
     * Returns if the access_token is expired.
     * @return bool Returns True if the access_token is expired.
     */
    public function isAccessTokenExpired()
    {
        $tokenKey = $this->session_token_key;

        if (! $this->session->get($tokenKey) || ! is_array($this->session->get($tokenKey))) {
            return true;
        }

        $token = $this->session->get($tokenKey);

        if (!isset($token['token_type'])) {
            return true;
        }

        if (!isset($token['expires_in'])) {
            return true;
        }

        if (!isset($token['access_token'])) {
            return true;
        }

        if (!isset($token['created_at'])) {
            return true;
        }

        // If the token is set to expire in the next 30 seconds.
        return ($token['created_at'] + ($token['expires_in'] - 30)) < time();
    }

    public function getNews(array $options = [])
    {
        return $this->getResources('news', $options);
    }

    public function getNewsById($id, array $options = [])
    {
        return $this->getResourceByid('news', $id, $options);
    }

    public function getAdverts(array $options = [])
    {
        return $this->getResources('adverts', $options);
    }

    public function getAdvertById($id, array $options = [])
    {
        return $this->getResourceByid('adverts', $id, $options);
    }

    public function getEvents(array $options = [])
    {
        return $this->getResources('events', $options);
    }

    public function getEventById($id, array $options = [])
    {
        return $this->getResourceByid('events', $id, $options);
    }

    public function getUserById($id, array $options = [])
    {
        return $this->getResourceByid('users', $id, $options);
    }

    /**
     * @param $resource
     * @param array $options
     *
     * @return mixed
     */
    private function getResources($resource, array $options = [])
    {
        $url = sprintf('/api/%s', $resource);

        return $this->get($resource, $url, $options);
    }

    /**
     * @param $resource
     * @param $id
     * @param array $options
     *
     * @return mixed
     */
    private function getResourceByid($resource, $id, array $options = [])
    {
        $url = sprintf('/api/%s/%s', $resource, $id);

        return $this->get($resource, $url, $options);
    }

    /**
     * @param $resource
     * @param $url
     * @param array $options
     *
     * @return mixed
     */
    private function get($resource, $url, array $options = [])
    {
        $tokenKey = $this->session_token_key;
        $token = $this->session->get($tokenKey);
        $options['query'] = ['access_token' => $token['access_token']];

        $response = $this->client->get($url, $options);

        if (200 !== $response->getStatusCode()) {
            throw new \LogicException('An error occurred when trying to GET data from MR API');
        }

        $jsonData = $response->json();
        if (!is_array($jsonData) || !isset($jsonData['request']) || !is_array($jsonData[$resource])) {
            throw new \LogicException('The response providing from MR API is not valid');
        }

        return $jsonData;
    }
}
