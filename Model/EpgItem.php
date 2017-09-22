<?php


namespace Model;


class EpgItem extends ActiveRecord
{
    protected $_table='epg';

    /**
     * @var string
     */
    protected $time;

    /**
     * @var string
     */
    protected $time_to;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $ch_id;

    /**
     * @return \DateTime
     */
    public function getTime()
    {
        return new \DateTime($this->time);
    }

    /**
     * @return \DateTime
     */
    public function getTimeTo()
    {
        return new \DateTime($this->time_to);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getChId()
    {
        return (int)$this->ch_id;
    }


}