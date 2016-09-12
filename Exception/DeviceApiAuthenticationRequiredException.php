<?php


namespace Exception;

class DeviceApiAuthenticationRequiredException extends DeviceApiException{
    public function __construct($message)
    {
        parent::__construct($message,403);
    }
}