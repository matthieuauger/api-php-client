<?php

namespace MaResidence\Component\ApiClient;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use GuzzleHttp\Client as GuzzleClient;

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
        $this->client = new GuzzleClient();
    }

    /**
     *
     */
    public function authenticate()
    {
        $token = $this->getToken();
        $token['created_at'] = time();
        $this->session->set($this->session_token_key, $token);
    }

    /**
     * @return array|bool|float|int|string
     * @throws \LogicException
     */
    public function getToken()
    {
        $url = $this->token_url;

        $request = $this->client->createRequest(
            'GET',
            $url,
            [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'password',
                'username' => $this->username,
                'password' => $this->password,
            ]
        );

        $response = $request->send();

        if (200 !== $response->getStatusCode()) {
            throw new \LogicException('An error occurred when trying to GET data to MR API');
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

        if (!isset($token['scope'])) {
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

    /**
     * {@inheritdoc}
     */
    public function getNews()
    {
        $tokenKey = $this->session_token_key;
        $url = $this->endpoint;

        $token = $this->session->get($tokenKey);

        $request = $this->client->get($url.'/news?access_token='.$token['access_token']);
        $response = $request->send();

        if (200 !== $response->getStatusCode()) {
            throw new \LogicException('An error occurred when trying to GET News from MR API');
        }

        $news = $response->json();
        if (!is_array($news) || !isset($news['request']) || !is_array($news['news'])) {
            return [];
        }

        return $news;
    }
}
