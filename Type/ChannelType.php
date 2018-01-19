<?php


namespace Type;


use Model\CasConfig;
use Model\Channel;


class ChannelType
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var int
     */
    public $number;

    /**
     * @var string
     */
    public $logo;

    /**
     * @var string
     */
    public $url;

    /**
     * @var int
     */
    public $age_group_id;

    /**
     * @var string
     */
    public $tshift_proto = null;

    /**
     * @var int
     */
    public $tshift_depth = null;

    /**
     * @var string
     */
    public $tshift_base_url = null;

    /**
     * @var int
     */

    public $tshift_cas_config_id = null;

    /**
     * @var int
     */
    public $cas_config_id = null;

    /**
     * @var MediaType
     */
    public $media;

    public function __construct(Channel $channel,CasConfig $defaultCasConfig,$baseLogoUrl)
    {
        $this->id = (int)$channel->getId();
        $this->title = $channel->getDisplayName();
        $this->number = (int)$channel->getDisplayNumber();
        $this->logo =  $channel->getLogo() ? $baseLogoUrl.$channel->getLogo():null;
        $this->url = $channel->getUrl();
        $this->age_group_id = $channel->isCensored() ? 0:null;

        if($channel->isEnableTvArchive() && $channel->getDvrServer()){
            $this->tshift_proto = $channel->getDvrServer()->getDvrServerType();
            $this->tshift_base_url = $channel->getDvrServer()->getTimeshiftUrl($channel->_storage, $channel);
            $this->tshift_depth = $channel->getTvArchiveDuration();
            $this->tshift_cas_config_id = null;

        }



        $this->media=new MediaType();
        $this->media->aspect = $channel->getAspectRatio();
//        if($channel->isNeedScramble()){
//            $channel->getCasConfig()? $this->cas_config_id = $channel->getCasConfig()->getId(): $this->cas_config_id = $defaultCasConfig->getId();
//        }
    }

}