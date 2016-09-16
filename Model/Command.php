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
     * @var bool
     */
    protected $reboot_after_ok;

    /**
     * @var bool
     */
    protected $need_confirm;

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return string
     */
    public function getMsg()
    {
        return $this->msg;
    }



    /**
     * @return boolean
     */
    public function isNeedConfirm()
    {
        return $this->need_confirm;
    }


    /**
     * @return boolean
     */
    public function isRebootAfterOk()
    {
        return (bool)$this->reboot_after_ok;
    }



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