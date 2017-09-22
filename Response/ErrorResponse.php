<?php

namespace Response;


class ErrorResponse implements ResponseInterface
{
    public $code;
    public $message;

    /**
     * ErrorResponse constructor.
     * @param $code
     * @param $message
     */
    public function __construct($code, $message)
    {
        $this->code = $code;
        $this->message = $message;
    }


}