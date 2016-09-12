<?php


namespace Exception;


class DeviceApiNotFoundException extends DeviceApiException {
    public function __construct($message)
    {
        parent::__construct($message,404);
    }
}