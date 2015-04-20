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
     * @inheritdoc
     */
    public function getAccessToken()
    {
        return isset($this->tokens['access_token']) ? $this->tokens['access_token'] : [];
    }

    /**
     * @inheritdoc
     */
    public function setAccessToken($token)
    {
        $this->tokens['access_token'] = $token;
    }

    /**
     * @inheritdoc
     */
    public function isAccessTokenExpired()
    {
        $token = $this->getAccessToken();

        if (isset($token['created_at']) && $token['expires_in']) {
            return ($token['created_at'] + $token['expires_in']) < time();
        }

        return true;
    }

}
