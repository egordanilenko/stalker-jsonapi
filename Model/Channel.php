<?php


namespace Model;


use Utils\DvrServerLoadBalancer;

class Channel extends ActiveRecord
{
    protected $_table='itv';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var integer
     */
    protected $number;

    /**
     * @var string
     */
    protected $cmd;

    /**
     * @var string
     * timeshifted stream
     */
    protected $mc_cmd;

    /**
     * @var bool
     */
    protected $censored;

    /**
     * @var string
     */
    protected $logo;

    /**
     * @var int
     */
    protected $tv_genre_id;

    public $_storage;


    /**
     * @var bool
     */
    protected $enable_tv_archive;


    protected $flussonic_dvr;
    protected $wowza_dvr;

    protected $tv_archive_duration;


    public function getDisplayNumber(){
        return $this->number;
    }

    public function getDisplayName(){
        return $this->name;
    }

    public function getLogo(){
        return $this->logo;
    }

    public function getUrl(){
        $explode = explode(' ',$this->cmd);
        return array_pop($explode);
    }

    public function getAgeGroup(){
        return null;
    }

    /**
     * @return DvrServer
     */
    public function getDvrServer(){

        if($this->mc_cmd && (bool)$this->enable_tv_archive && (bool) $this->flussonic_dvr){
            $server = new DvrServerFlussonic();
            $server->setDefaultTimeShiftDepthSeconds((int)$this->tv_archive_duration*3600);
            return $server;
        } elseif ($this->mc_cmd && (bool)$this->enable_tv_archive  && !(bool) $this->flussonic_dvr && !(bool) $this->wowza_dvr) {
            $server = new DvrServerStalker();
            $server->setChannelNumber($this->getId());

            $server->setDefaultTimeShiftDepthSeconds((int)$this->tv_archive_duration*3600);
            return $server;
        }
        return null;
    }

    public function setStorage($storage) {
        $this->_storage = $storage;
    }

    public function getStorage() {
        $this->_storage;
    }


    public function getAspectRatio(){
        return null;
    }
    /**
     * @return int
     */
    public function getTvGenreId()
    {
        return $this->tv_genre_id;
    }


    public function isCensored(){
        return (bool)$this->censored;
    }

    /**
     * @return boolean
     */
    public function isEnableTvArchive()
    {
        return (bool)$this->enable_tv_archive;
    }

    /**
     * @return mixed
     */
    public function getFlussonicDvr()
    {
        return $this->flussonic_dvr;
    }

    /**
     * @return mixed
     */
    public function getTvArchiveDuration()
    {
        return (int)$this->tv_archive_duration*3600;
    }

    public function getTimeShiftUrl(){
        return $this->mc_cmd;
    }



}