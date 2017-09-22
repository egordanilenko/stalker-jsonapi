<?php

namespace Response;


use Model\CasConfig;
use Type\AgeGroupType;
use Type\GroupType;
use Type\ChannelType;


class ChannelsResponse  implements ResponseInterface
{
    /**
     * @var int
     */
    public $channels_version;

    /**
     * @var string
     */

    public $channels_hash;
    /**
     * @var int
     */
    public $epg_version;

    /**
     * @var ChannelType[]
     */
    public $channels=array();

    /**
     * @var GroupType[]
     */
    public $groups=array();

    /**
     * @var AgeGroupType[]
     */
    public $age_groups=array();

    /**
     * ChannelsResponse constructor.
     * @param $channels_version
     * @param $epg_version
     * @param array $channels
     * @param array $ageGroups
     * @param array $favorites
     * @param $channels_hash
     * @param CasConfig $defaultCasConfig
     * @param $baseLogoUrl
     */
    public function __construct($channels_version, $epg_version, array $channels, array $ageGroups,array $favorites, $channels_hash ,CasConfig $defaultCasConfig, $baseLogoUrl)
    {
        $this->channels_version = $channels_version;
        $this->epg_version = $epg_version;
        $this->channels_hash = $channels_hash;

        foreach($favorites as $favoriteGroup){
            $groupType = new GroupType($favoriteGroup,$channels);
            if(count($groupType->items)>0){
                array_push($this->groups,$groupType);
            }
        }

        foreach($channels as $channel){
            array_push($this->channels,new ChannelType($channel,$defaultCasConfig,$baseLogoUrl));
        }


        foreach($ageGroups as $age){
            array_push($this->age_groups,new AgeGroupType($age));
        }

    }


}