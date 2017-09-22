<?php


namespace Exception;


class DeviceApiIncorrectCredintialsExcption extends DeviceApiException{
    public function __construct($message)
    {
        parent::__construct($message,401);
    }
}