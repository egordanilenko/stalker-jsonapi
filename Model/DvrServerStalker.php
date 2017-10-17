<?php


namespace Model;


class DvrServerStalker extends DvrServer
{
    private $channel_number;

    /**
     * @param mixed $channel_number
     */
    public function setChannelNumber($channel_number)
    {
        $this->channel_number = $channel_number;
    }



    public function getTimeshiftUrl($baseUrl)
    {
        $baseUrl=str_replace('index.m3u8','',$baseUrl);
        $baseUrl=trim($baseUrl,'/');

        $uri = substr($_SERVER{'REQUEST_URI'},0,strripos($_SERVER['REQUEST_URI'],'/json/'));

        return
            'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].
            $uri.'/json/archive/'.$this->channel_number.'/'.
            $this->getVariable('%s').
            '/index.m3u8';


    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param mixed $defaultTimeShiftDepthSeconds
     */
    public function setDefaultTimeShiftDepthSeconds($defaultTimeShiftDepthSeconds)
    {
        $this->defaultTimeShiftDepthSeconds = $defaultTimeShiftDepthSeconds;
    }

    /**
     * @return string
     */
    public function getDvrServerType(){
        return "STALKER";
    }


}