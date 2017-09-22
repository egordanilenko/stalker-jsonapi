<?php


namespace Type;


use Model\Channel;

class GroupItemType{
    public $id;
    public function __construct(Channel $channel)
    {
        $this->id = $channel->getId();
    }
}