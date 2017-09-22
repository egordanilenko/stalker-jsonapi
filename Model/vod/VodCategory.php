<?php


namespace Model\vod;


class VodCategory
{

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * VodTag constructor.
     * @param int $id
     * @param string $title
     */
    public function __construct($id, $name)
    {
        $this->id=(int)$id;
        $this->name = $name;
    }
}