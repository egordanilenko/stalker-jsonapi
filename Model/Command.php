<?php


namespace Model;


class Command extends ActiveRecord
{
    protected $_table='events';

    /**
     * @var int
     */
    protected $uid;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $event;

    /**
     * @var string
     */
    protected $header;

    /**
     * @var string
     */
    protected $msg;

    /**
     * @var string
     */
    protected $addtime;

    /**
     * @var string
     */
    protected $eventtime;

    /**
     * @var bool
     */
    protected $sended;

    /**
     * @var bool
     */
    protected $ended;

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return mixed
     */
    public function getSended()
    {
        return $this->sended;
    }

    /**
     * @param mixed $sended
     */
    public function setSended($sended)
    {
        $this->sended = $sended;
    }

    /**
     * @return mixed
     */
    public function getEnded()
    {
        return $this->ended;
    }

    /**
     * @param mixed $ended
     */
    public function setEnded($ended)
    {
        $this->ended = $ended;
    }
}