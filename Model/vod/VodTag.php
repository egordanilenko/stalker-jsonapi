<?php


namespace Model\vod;


class VodTag
{

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * VodTag constructor.
     * @param int $id
     * @param string $title
     */
    public function __construct($id, $title)
    {
        $this->id = (int)$id;
        $this->title = $title;
    }
}