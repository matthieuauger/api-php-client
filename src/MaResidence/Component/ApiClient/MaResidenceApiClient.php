<?php

namespace MaResidence\Component\ApiClient;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MaResidenceApiClient
{
    const MR_API_URL = "https://www.ma-residence.fr/api/";
    const MR_OAUTH_TOKEN_KEY = 'mr_api_client.oauth_token';
    const MR_API_OAUTH_URL_TOKEN = "https://www.ma-residence.fr/oauth/v2/apitoken";
    const MR_SANDBOX_API_URL = "https://www.preprod.ma-residence.fr/api/";
    const MR_SANDBOX_OAUTH_TOKEN_KEY = 'mr_sandbox_api_client.oauth_token';
    const MR_SANDBOX_API_OAUTH_URL_TOKEN = "https://www.preprod.ma-residence.fr/oauth/v2/apitoken";

    /**
     * @var \GuzzleHttp\Client
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
     * @var bool Activate sandbox mode
     */
    private $sandbox;

    /**
     * @param SessionInterface $session
     * @param $clientId
     * @param $clientSecret
     * @param $username
     * @param $password
     * @param $sandbox
     */
    public function __construct(SessionInterface $session, $clientId, $clientSecret, $username, $password, $sandbox = false)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->username = $username;
        $this->password = $password;
        $this->sandbox = $sandbox;
        $this->client = new \GuzzleHttp\Client();
    }

    /**
     *
     */
    public function authenticate()
    {
        $token = $this->getToken();
        $token['created_at'] = time();
        $this->session->set(self::MR_OAUTH_TOKEN_KEY, $token);
    }

    /**
     * @return array|bool|float|int|string
     * @throws \LogicException
     */
    public function getToken()
    {
        $url = self::MR_API_OAUTH_URL_TOKEN;

        if ($this->sandbox) {
            $url = self::MR_SANDBOX_API_OAUTH_URL_TOKEN;
        }

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
        $tokenKey = self::MR_OAUTH_TOKEN_KEY;

        if ($this->sandbox) {
            $tokenKey = self::MR_SANDBOX_OAUTH_TOKEN_KEY;
        }

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
        $tokenKey = self::MR_OAUTH_TOKEN_KEY;
        $url = self::MR_API_URL;

        if ($this->sandbox) {
            $tokenKey = self::MR_SANDBOX_OAUTH_TOKEN_KEY;
            $url = self::MR_SANDBOX_API_URL;
        }

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
