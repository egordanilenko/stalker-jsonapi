<?php

namespace Type;


use Model\Channel;
use Model\EpgItem;
use Utils\QueryBuilder;

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
        $this->channel_id = (int)$channel->getId();


        $dateTime = new \DateTime();
        $dateTime->format('c');
        $sql = "SELECT id FROM epg WHERE time >= NOW() OR (time <= NOW() AND time_to >= NOW()) AND ch_id=$this->channel_id ORDER BY time ASC LIMIT 0,3";
        $query = QueryBuilder::query($sql);
        $epgs = array();
        while($record = $query->fetch_assoc()){
            array_push($epgs, new EpgItem($record['id']));
        }


        foreach ($epgs as $epg){
            array_push($this->events, new EpgType($epg));
        }
    }
}