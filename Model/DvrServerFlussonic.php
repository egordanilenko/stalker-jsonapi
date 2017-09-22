<?php


namespace Model;


class DvrServerFlussonic extends DvrServer
{
    public function getTimeshiftUrl($baseUrl)
    {

        $baseUrl=str_replace('index.m3u8','',$baseUrl);
        $baseUrl=trim($baseUrl,'/');
        /**
         *Example http://flussonic:8080/channel/timeshift_abs_mono-1350274200.m3u8
         * retrurn full url with specific variables
         */
        return
            $baseUrl.
            '/timeshift_abs_mono-'.
            $this->getVariable('%s').
            '.m3u8';


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