<?php

namespace Response;


class AuthResponse implements ResponseInterface
{
    /**
     * @var bool
     */
    public $registered;

    /**
     * @var string
     * enum: mac, login
     */
    public $register_type;

    /**
     * @var string
     * enum: mac, token, cookie, header, param
     */
    public $session_type;

    /**
     * @var string
     */
    public $token;

    /**
     * AuthResponse constructor.
     * @param bool $registered
     * @param string $register_type
     * @param string $session_type
     * @param string $token
     */
    public function __construct($registered, $register_type, $session_type, $token)
    {
        $this->registered = $registered;
        $this->register_type = $register_type;
        $this->session_type = $session_type;
        $this->token = $token;
    }


}