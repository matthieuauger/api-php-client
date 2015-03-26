<?php

namespace MaResidence\Component\ApiClient;

use MaResidence\Component\ApiClient\TokenStorageInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use MaResidence\Component\ApiClient\Exception\BadRequestException;
use MaResidence\Component\ApiClient\Exception\InvalidClientException;
use MaResidence\Component\ApiClient\Exception\UnauthorizedClientException;

class Client
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

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
    private $tokenUrl;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param string           $clientId
     * @param string           $clientSecret
     * @param string           $username
     * @param string           $password
     * @param string           $endpoint
     * @param string           $tokenUrl
     */
    public function __construct(TokenStorageInterface $tokenStorage, $clientId, $clientSecret, $username, $password, $endpoint, $tokenUrl)
    {
        $this->tokenStorage = $tokenStorage;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->username = $username;
        $this->password = $password;
        $this->endpoint = $endpoint;
        $this->tokenUrl = $tokenUrl;
        $this->client = new GuzzleClient(['base_url' => $endpoint]);
    }

    /**
     * Authenticate user through the API
     */
    public function authenticate()
    {
        $token = $this->getToken();
        $token['created_at'] = time();

        $this->setAccessToken($token);

    }

    /**
     * Set access token
     *
     * @param array $token
     */
    public function setAccessToken($token)
    {
        return $this->tokenStorage->setAccessToken($token);
    }

    /**
     * @return mixed
     * @throws BadRequestException
     * @throws InvalidClientException
     * @throws UnauthorizedClientException
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
            $response = $this->client->get($this->tokenUrl, $options);
        } catch (BadRequestException $e) {
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
     * Returns if the access token is expired.
     *
     * @return bool Returns true if the access token is expired.
     */
    public function isAccessTokenExpired()
    {
        return $this->tokenStorage->isAccessTokenExpired();
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->tokenStorage->getAccessToken();
    }

    /**
     * @param array $options
     *
     * @return mixed
     */
    public function getNews(array $options = [])
    {
        return $this->getResources('news', $options);
    }

    /**
     * @param $id
     * @param array $options
     *
     * @return mixed
     */
    public function getNewsById($id, array $options = [])
    {
        return $this->getResourceById('news', $id, $options);
    }

    /**
     * @param array $options
     *
     * @return mixed
     */
    public function getAdverts(array $options = [])
    {
        return $this->getResources('adverts', $options);
    }

    /**
     * @param $id
     * @param array $options
     *
     * @return mixed
     */
    public function getAdvertById($id, array $options = [])
    {
        return $this->getResourceById('adverts', $id, $options);
    }

    /**
     * @param array $options
     *
     * @return mixed
     */
    public function getAdvertCategories(array $options = [])
    {
        return $this->getResources('advertcategories', $options);
    }

    /**
     * @param $id
     * @param array $options
     *
     * @return mixed
     */
    public function getAdvertCategoryById($id, array $options = [])
    {
        return $this->getResourceById('advertcategories', $id, $options);
    }

    /**
     * @param array $options
     *
     * @return mixed
     */
    public function getEvents(array $options = [])
    {
        return $this->getResources('events', $options);
    }

    /**
     * @param $id
     * @param array $options
     *
     * @return mixed
     */
    public function getEventById($id, array $options = [])
    {
        return $this->getResourceById('events', $id, $options);
    }

    /**
     * @param $id
     * @param array $options
     *
     * @return mixed
     */
    public function getHabitationById($id, array $options = [])
    {
        return $this->getResourceById('habitations', $id, $options);
    }

    /**
     * @param $id
     * @param array $options
     *
     * @return mixed
     */
    public function getHabitationGroupById($id, array $options = [])
    {
        return $this->getResourceById('habitationgroups', $id, $options);
    }

    /**
     * @param $id
     * @param array $options
     *
     * @return mixed
     */
    public function getRecommendationById($id, array $options = [])
    {
        return $this->getResourceById('recommendations', $id, $options);
    }

    /**
     * @param array $options
     *
     * @return mixed
     */

    /**
     * @param array $userData
     * @param       $version
     *
     * @return array
     */
    public function postUser(array $userData, $version)
    {
        $data['user'] = $userData;

        try {
            $response = $this->post('/api/users', $version, $data);
            $body = $response->json();
        } catch (RequestException $e) {
            // If user already exists
            if ($e->getCode() == 409 && null !== $e->getResponse()) {
                $body = $e->getResponse()->json();
            }
        }

        if (! is_array($body) || ! array_key_exists('user', $body)) {
            throw new \LogicException(
                'The User was successfully created but an unexpected response was return from the MR API'
            );
        }

        $user = $body['user'];

        if (! is_array($user) || ! array_key_exists('id', $user) || ! array_key_exists('self', $user)) {
            throw new \LogicException(
                'The User was successfully created but an unexpected response was return from the MR API. Expected key id and self.'
            );
        }

        return $body['user'];
    }

    /**
     * @param array $advertData
     * @param       $version
     *
     * @return mixed
     */
    public function postAdvert(array $advertData, $version)
    {
        $data['advert'] = $advertData;
        $response = $this->post('/api/adverts', $version, $data);

        $body = $response->json();

        if (! is_array($body) || ! array_key_exists('advert', $body)) {
            throw new \LogicException(
                'The Advert was successfully created but an unexpected response was return from the MR API'
            );
        }

        $advert = $body['advert'];

        if (! is_array($advert) || ! array_key_exists('id', $advert) || ! array_key_exists('self', $advert)) {
            throw new \LogicException(
                'The Advert was successfully created but an unexpected response was return from the MR API. Expected key id and self.'
            );
        }

        return $body['advert'];
    }

    /**
     * @param array $recommendationData
     * @param       $version
     *
     * @return mixed
     */
    public function postRecommendation(array $recommendationData, $version)
    {
        $data['recommendation'] = $recommendationData;
        $response = $this->post('/api/recommendations', $version, $data);

        $body = $response->json();

        if (! is_array($body) || ! array_key_exists('recommendation', $body)) {
            throw new \LogicException(
                'The Recommendation was successfully created but an unexpected response was return from the MR API'
            );
        }

        $recommendation = $body['recommendation'];

        if (! is_array($recommendation) || ! array_key_exists('id', $recommendation) || ! array_key_exists('self', $recommendation)) {
            throw new \LogicException(
                'The Recommendation was successfully created but an unexpected response was return from the MR API. Expected key id and self.'
            );
        }

        return $body['recommendation'];
    }

    /**
     * @param string $id ID of the advert to share
     * @param array $shareData
     * @param       $version
     *
     * @return mixed
     */
    public function postAdvertShare($id, array $shareData, $version)
    {
        $data['share'] = $shareData;
        $url = sprintf('/api/adverts/%s/shares', $id);

        $response = $this->post($url, $version, $data);

        $body = $response->json();

        if (! is_array($body) || ! array_key_exists('share', $body)) {
            throw new \LogicException(
                'The Share was successfully created but an unexpected response was return from the MR API'
            );
        }

        $share = $body['share'];

        if (! is_array($share) || ! array_key_exists('email', $share)) {
            throw new \LogicException(
                'The Share was successfully created but an unexpected response was return from the MR API. Expected key email.'
            );
        }

        return $body['share'];
    }

    /**
     * @param $id
     * @param array $options
     *
     * @return mixed
     */
    public function getUserById($id, array $options = [])
    {
        return $this->getResourceById('users', $id, $options);
    }

    /**
     * @param array $options
     *
     * @return mixed
     */
    public function getAssociations(array $options = [])
    {
        return $this->getResources('associations', $options);
    }

    /**
     * @param $id
     * @param array $options
     *
     * @return mixed
     */
    public function getAssociationById($id, array $options = [])
    {
        return $this->getResourceById('associations', $id, $options);
    }

    /**
     * @param array $options
     *
     * @return mixed
     */
    public function getShops(array $options = [])
    {
        return $this->getResources('shops', $options);
    }

    /**
     * @param $id
     * @param array $options
     *
     * @return mixed
     */
    public function getShopById($id, array $options = [])
    {
        return $this->getResourceById('shops', $id, $options);
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

        return $this->get($url, $options);
    }

    /**
     * @param $resource
     * @param $id
     * @param array $options
     *
     * @return mixed
     */
    private function getResourceById($resource, $id, array $options = [])
    {
        $url = sprintf('/api/%s/%s', $resource, $id);

        return $this->get($url, $options);
    }

    /**
     * @param $url
     * @param array $options
     *
     * @return array
     */
    private function get($url, array $options = [])
    {
        $token = $this->tokenStorage->getAccessToken();

        $requestOptions = [];

        foreach ($options as $key => $value) {
            if ($key != 'version' && $key != 'access_token') {
                $requestOptions['query'][$key] = $value;
            }
        }

        if (array_key_exists('version', $options)) {
            $requestOptions['headers']['Accept'] = sprintf('application/ma-residence.v%d', $options['version']);
        }

        $requestOptions['query']['access_token'] = $token['access_token'];

        $response = $this->client->get($url, $requestOptions);

        if (200 !== $response->getStatusCode()) {
            throw new \LogicException('An error occurred when trying to GET data from MR API');
        }

        $data = $response->json();
        if (!is_array($data)) {
            throw new \LogicException('The response providing from MR API is not valid');
        }

        return $data;
    }

    /**
     * @param $url
     * @param $version
     * @param array $data
     * @param string $bodyEncoding
     *
     * @return \GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null
     */
    private function post($url, $version, array $data = [], $bodyEncoding = 'json')
    {
        $token = $this->tokenStorage->getAccessToken();

        $requestOptions = [];

        foreach ($data as $key => $value) {
            $requestOptions['body'][$key] = $value;
        }

        // Encode the body to be fully compatible with REST
        if ('json' == $bodyEncoding) {
            $requestOptions['body'] = json_encode($requestOptions['body']);
        }

        $requestOptions['headers']['Accept'] = sprintf('application/ma-residence.v%d', $version);

        $requestOptions['query']['access_token'] = $token['access_token'];

        $response = $this->client->post($url, $requestOptions);

        if (201 !== $response->getStatusCode()) {
            throw new \LogicException('An error occurred when trying to POST data to MR API');
        }

        return $response;
    }
}
