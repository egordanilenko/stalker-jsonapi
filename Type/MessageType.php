<?php

namespace Type;


use Model\Command;

class MessageType
{

    public static  $mapping = array(
        'update_epg' => 'refresh_channel_list',
        'cut_off'    => 'refresh_channel_list',
        'cut_on'     => 'refresh_channel_list',
        'reboot'     => 'restart',
        'send_msg'   => 'user_message',
        'reinit'     => 'reinit'
    );

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $command;

    /**
     * @var int
     */
    public $ttl;

    /**
     * @var \stdClass
     */
    public $args;

    public function __construct(Command $command)
    {
        if(!array_key_exists($command->getEvent(),self::$mapping)) throw new \Exception("Mapping for ".$command->getEvent()." not found");
        $this->id = $command->getId();
        $command->isRebootAfterOk() ?  $this->command='reinit':$this->command = self::$mapping[$command->getEvent()];
        $this->args = (object)array();
        if($this->command=='user_message'){
            $this->args = array(
                'type'  => $command->isNeedConfirm() ? 'confirm' : 'notify',
                'title' => $command->getHeader(),
                'text'  => $command->getMsg()
            );
        }

    }
}