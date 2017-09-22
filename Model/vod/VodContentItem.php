<?php

namespace Model\vod;

class VodContentItem {

    public $id;

    public $title;

    public $items;

    public $children;

    /**
     * VodContentItem constructor.
     * @param $id
     * @param $title
     * @param $items
     * @param $children
     */
    public function __construct($id, $title, $items, $children)
    {
        $this->id = (int)$id;
        $this->title = $title;
        $this->items = $items;
        $this->children = $children;
    }


}