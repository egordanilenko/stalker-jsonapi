<?php

namespace Response;


class UserInfoResponse implements ResponseInterface
{
    /**
     * @var string
     */
    public $fullname;

    /**
     * @var string
     */
    public $contract;

    /**
     * @var boolean
     */
    public $enabled;

    /**
     * @var integer
     */
    public $devices_limit;

    /**
     * @var integer
     */
    public $devices_used;

    /**
     * UserInfoResponse constructor.
     * @param string $fullname
     * @param string $contract
     * @param bool $enabled
     * @param int $devices_limit
     * @param int $devices_used
     */
    public function __construct($fullname, $contract, $enabled, $devices_limit, $devices_used)
    {
        $this->fullname = $fullname;
        $this->contract = $contract;
        $this->enabled = $enabled;
        $this->devices_limit = $devices_limit;
        $this->devices_used = $devices_used;
    }


}