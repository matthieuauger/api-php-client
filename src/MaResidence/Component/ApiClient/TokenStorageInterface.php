<?php

namespace MaResidence\Component\ApiClient;

/**
 * TokenStorageInterface
 */
interface TokenStorageInterface
{
    /**
     * Returns the current security token.
     *
     * @return mixed|null A token or null if no authentication information is available
     */
    public function getAccessToken();

    /**
     * Sets the authentication token.
     *
     * @param array $token A token or null if no further authentication information should be stored
     */
    public function setAccessToken($token);

    /**
     * Checks if authentication token has expired
     *
     * @return bool
     */
    public function isAccessTokenExpired();
}
