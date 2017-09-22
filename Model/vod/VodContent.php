<?php


namespace Model\vod;


class VodContent
{

    public $id;

    public $title;

    public $poster_path;

    public $original_title;

    public $year;

    public $description;
    public $duration_min;
    public $age_group_id;
    public $category_id;
    public $tags_id;
    public $items;
    public $team;
    public $children;
    public $age_groups;

    /**
     * VodContent constructor.
     * @param $original_title
     * @param $year
     * @param $description
     * @param $duration_min
     * @param $age_group_id
     * @param $category_id
     * @param $tags_id
     * @param $items
     * @param $team
     * @param $children
     * @param $age_groups
     */
    public function __construct($id,
                                $title,
                                $poster_path,
                                $original_title,
                                $year,
                                $description,
                                $duration_min,
                                $age_group_id,
                                $category_id,
                                $tags_id,
                                $items,
                                $team,
                                $children,
                                $age_groups)
    {
        $this->id = (int)$id;
        $this->title = $title;
        $this->poster_path = $poster_path;
        $this->original_title = $original_title;
        $this->year = (int)$year;
        $this->description = $description;
        $this->duration_min = (int)$duration_min;
        $this->age_group_id = (int)$age_group_id;
        $this->category_id = (int)$category_id;
        $this->tags_id = $tags_id;
        $this->items = $items;
        $this->team = $team;
        $this->children = $children;
        $this->age_groups = $age_groups;
    }


}