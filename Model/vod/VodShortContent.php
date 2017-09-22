<?php


namespace Model\vod;


class VodShortContent
{

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    public $poster_path;

    /**
     * VodTag constructor.
     * @param int $id
     * @param string $title
     */
    public function __construct($id, $title,$poster_path)
    {
        $this->id=$id;
        $this->title = $title;
        $this->poster_path = $poster_path;
    }
}