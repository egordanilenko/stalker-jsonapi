<?php

namespace Response;


use Type\AgeGroupType;

class ShortEpgResponse implements ResponseInterface
{
    /**
     * @var int
     */
    public $version;

    /**
     * @var array
     */
    public $channels =array();

    /**
     * @var array
     */
    public $age_groups = array();

    /**
     * ShortEpgResponse constructor.
     * @param $version
     * @param array $channels
     * @param array $age_groups
     */
    public function __construct($version, array $channels, array $age_groups)
    {
        $this->version  = $version;
        $this->channels = $channels;
        foreach($age_groups as $age_group){
            array_push($this->age_groups, new AgeGroupType($age_group));
        }
    }

}