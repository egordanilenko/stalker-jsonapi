<?php

namespace Type;


use Model\EpgItem;
class EpgType
{
    /**
     * @var int
     */
    public $start;

    /**
     * @var int
     */
    public $end;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string;
     */
    public $description;

    /**
     * @var int
     */
    public $age_group_id;

    public function __construct(EpgItem $epg)
    {
        $this->start=$epg->getTime()->getTimestamp();
        $this->end=$epg->getTimeTo()->getTimestamp();
        $this->title=$epg->getName();
        $this->description=null;
        $this->age_group_id = null;
    }
}