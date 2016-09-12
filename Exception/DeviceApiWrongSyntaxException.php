<?php


namespace Exception;


class DeviceApiWrongSyntaxException extends DeviceApiException{
    public function __construct($message)
    {
        parent::__construct($message,400);
    }
}