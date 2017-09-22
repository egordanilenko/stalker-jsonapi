<?php

namespace Model\vod;

class VodContentFileItem {

    public $id;

    public $title;

    public $url;

    public $type_id;

    /*
     * Усталкера типы(db: video_series_files.quality) видео:
        1 - 240
        2 - 320
        3 - 480
        4 - 576
        5 - 720
        6 - 1080
        7 - 4к
     */
     private $type_map =[
         1 => 1,
         2 => 1,
         3 => 1,
         4 => 1,
         5 => 2,
         6 => 2,
         7 => 3,
        ];

    private $type_name = [
        1 => 'SD',
        2 => 'HD',
        3 => 'UHD'
    ];

    /**
     * VodContentFileItem constructor.
     * @param $id
     * @param $title
     * @param $url
     * @param $type_id
     */
    public function __construct($id, $title, $url, $type_id)
    {
        $this->id = (int)$id;
        $this->type_id = $this->type_map[(int)$type_id];
        $this->title = $title." ".$this->type_name[$this->type_id];
        $this->url = $url;
    }


}