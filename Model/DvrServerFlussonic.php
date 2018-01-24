<?php


namespace Model;


class DvrServerFlussonic extends DvrServer
{
    public function getTimeshiftUrl($storage, Channel $channel)
    {

        $url = null;

        if (preg_match("/:\/\/([^\/]*)\/([^\/]*).*(m3u8)$/", $channel->getTimeShiftUrl(), $match)){

            $url = preg_replace('/:\/\/([^\/]*)/', '://'.$storage->storage_ip .":" .$storage->apache_port, $channel->getTimeShiftUrl());

            /**
             *Example http://flussonic:8080/channel/timeshift_abs_mono-1350274200.m3u8
             * retrurn full url with specific variables
             */
//            $url = preg_replace('/\index.m3u8/', 'timeshift_abs_mono-'.
//                $this->getVariable('%s').
//                '.m3u8', $url);
		$url = preg_replace('/\.m3u8/', '-' . $this->getVariable('%s') . '-now' . '.m3u8', $url);



        }

        return $url;


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
        return "FLUSSONIC";
    }


}