<?php

namespace Model\archive;

class Segment {

    private $start_time;
    private $end_time;
    private $start_byte;
    private $end_byte;
    private $duration;

    /**
     * Segment constructor.
     */
    public function __construct($start_time,$end_time,$start_byte,$end_byte,$duration)
    {
        $this->start_time = $start_time;
        $this->end_time = $end_time;
        $this->start_byte = $start_byte;
        $this->end_byte = $end_byte;
        $this->duration = $duration;
    }

    /**
     * @return mixed
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    /**
     * @param mixed $start_time
     */
    public function setStartTime($start_time)
    {
        $this->start_time = $start_time;
    }

    /**
     * @return mixed
     */
    public function getEndTime()
    {
        return $this->end_time;
    }

    /**
     * @param mixed $end_time
     */
    public function setEndTime($end_time)
    {
        $this->end_time = $end_time;
    }

    /**
     * @return mixed
     */
    public function getStartByte()
    {
        return $this->start_byte;
    }

    /**
     * @param mixed $start_byte
     */
    public function setStartByte($start_byte)
    {
        $this->start_byte = $start_byte;
    }

    /**
     * @return mixed
     */
    public function getEndByte()
    {
        return $this->end_byte;
    }

    /**
     * @param mixed $end_byte
     */
    public function setEndByte($end_byte)
    {
        $this->end_byte = $end_byte;
    }

    /**
     * @return mixed
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param mixed $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    public function getFileName(){
        return date("Ymd-H",$this->start_time).".mpg";
    }

    public function getIndexFileName(){
        return date("Ymd-H",$this->start_time).".idx";
    }
}