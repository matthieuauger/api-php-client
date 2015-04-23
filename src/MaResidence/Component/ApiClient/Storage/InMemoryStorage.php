<?php

namespace MaResidence\Component\ApiClient\Storage;

use MaResidence\Component\ApiClient\TokenStorageInterface;

class InMemoryStorage implements TokenStorageInterface
{
    private $tokens;

    public function __construct()
    {
        $this->tokens = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        return isset($this->tokens['access_token']) ? $this->tokens['access_token'] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function setAccessToken($token)
    {
        $this->tokens['access_token'] = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccessTokenExpired()
    {
        $token = $this->getAccessToken();

        if (null === $token) {
            return true;
        }

        if (!isset($token['created_at']) || !isset($token['expires_in'])) {
            return true;
        }

        $expirationTime = $token['created_at'] + $token['expires_in'];
        if ($expirationTime < time()) {
            return true;
        }

        return false;
    }
}
