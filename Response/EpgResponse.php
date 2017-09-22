<?php

namespace Response;


use Model\AgeGroup;
use Model\EpgItem;
use Type\AgeGroupType;
use Type\EpgType;


class EpgResponse implements ResponseInterface
{
    /**
     * @var int
     */
    public $version;

    /**
     * @var int
     */
    public $channel_id;

    /**
     * @var string
     * format Y-m-d
     */
    public $date;

    /**
     * @var EpgType[]
     */
    public $events=array();

    /**
     * @var AgeGroupType[]
     */
    public $age_groups=array();

    /**
     * EpgResponse constructor.
     * @param int $version
     * @param int $channel_id
     * @param \DateTime $date
     * @param EpgItem[] $events
     * @param AgeGroup[] $ageGroups
     */
    public function __construct($version, $channel_id, \DateTime $date, array $events, array $ageGroups)
    {
        $this->version      = (int)$version;
        $this->channel_id   = (int)$channel_id;
        $this->date          = $date->format('Y-m-d');

        foreach($events as $epg){
            array_push($this->events,new EpgType($epg));
        }

        foreach($ageGroups as $age){
            array_push($this->age_groups,new AgeGroupType($age));
        }

    }


}