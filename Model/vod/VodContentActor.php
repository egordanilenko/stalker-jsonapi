<?php

namespace Model\vod;

class VodContentActor {

    public $person_id;

    public $fullname;

    public $title;

    /**
     * VodContentActor constructor.
     * @param $person_id
     * @param $fullname
     * @param $title
     */
    public function __construct($person_id, $fullname, $title)
    {
        $this->person_id = (int)$person_id;
        $this->fullname = $fullname;
        $this->title = $title;
    }


}
