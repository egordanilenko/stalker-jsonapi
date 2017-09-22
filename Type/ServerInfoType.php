<?php

namespace Type;


class ServerInfoType
{
    /**
     * @var int
     */
    public $proto_version;

    /**
     * @var string
     */
    public $service_provider;

    /**
     * @var int
     */
    public $server_time;

    /**
     * @var bool
     */
    public $auth;

     /**
     * @var int
     */

    public $channel_list_update_interval;

    /**
     * @var PollType;
     */
    public $poll;

}