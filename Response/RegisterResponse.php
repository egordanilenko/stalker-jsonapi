<?php

namespace Response;


class RegisterResponse implements ResponseInterface
{
    /**
     * @var string
     */
    public $token;

    /**
     * RegisterResponse constructor.
     * @param string $token
     */
    public function __construct($token=null)
    {
        $this->token = $token;
    }


}