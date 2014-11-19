<?php

namespace MaResidence\Component\ApiClient\Exception;

use \Exception;

class BadRequestException extends Exception
{
    private $reasonPhrase;

    private $statusCode;

    private $url;

    private $body;

    public function __construct($message, $reasonPhrase, $statusCode, $url, array $body = [], Exception $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);

        $this->reasonPhrase = $reasonPhrase;
        $this->statusCode = $statusCode;
        $this->url = $url;
        $this->body = $body;
    }
}
