<?php


namespace Exception;


class DeviceApiRegistrationRequiredException extends DeviceApiException{
    public function __construct($message)
    {
        parent::__construct($message,401);
    }
}
