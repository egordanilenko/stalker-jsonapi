<?php

namespace Type;


use Model\Channel;
class ShortEpgType
{
    /**
     * @var int
     */

    public $channel_id;

    /**
     * @var EpgType[]
     */
    public $events =array();


    public function __construct(Channel $channel)
    {
        $this->channel_id = $channel->getId();
        if($channel->getCurrentEpg()) array_push($this->events, new EpgType($channel->getCurrentEpg()));
        $epgs = $channel->getCurrentNextEpg(3-count($this->events));
        foreach ($epgs as $epg){
            array_push($this->events, new EpgType($epg));
        }
    }
}