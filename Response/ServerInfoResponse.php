<?php


namespace Response;



use Type\CasConfigType;
use Type\PollType;

class ServerInfoResponse implements ResponseInterface
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
     * @var int
     */
    public $tz_offset;

    /**
     * @var bool
     */
    public $auth

     /**
     * @var int
     */;
    public $channel_list_update_interval;

    /**
     * @var string
     */
    public $remote_addr;

    /**
     * @var PollType
     */
    public $poll;
    
    /**
     * @var CasConfigType[]
     */
    public $cas_configs=array();

    /**
     * ServerInfoResponse constructor.
     * @param int $proto_version
     * @param string $service_provider
     * @param int $server_time
     * @param bool $auth
     * @param $channel_list_update_interval
     * @param string $remote_address
     * @param PollType $poll
     * @param CasConfigType[] $casTypes
     */
    public function __construct($proto_version, $service_provider, $server_time, $auth, $channel_list_update_interval, $remote_address, PollType $poll, array $casTypes)
    {
        $tz = new \DateTime();
        $this->proto_version = $proto_version;
        $this->service_provider = $service_provider;
        $this->server_time = $server_time;
        $this->auth = $auth;
        $this->channel_list_update_interval = $channel_list_update_interval;
        $this->poll = $poll;
        $this->cas_configs = $casTypes;
        $this->tz_offset = $tz->getOffset();
        $this->remote_addr = $remote_address;
    }

}