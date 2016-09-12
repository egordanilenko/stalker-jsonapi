<?php

namespace Type;



use Model\Channel;
use Model\FavoriteGroup;


class GroupType
{
    /**
     * @var int;
     */
    public $id;

    /**
     * @var string;
     */
    public $title;

    /**
     * @var $items
     */
    public $items=array();

    /**
     * GroupType constructor.
     * @param $favoriteGroup FavoriteGroup
     * @param $channels Channel[]
     */
    public function __construct(FavoriteGroup $favoriteGroup,array $channels)
    {
        $this->id = (int)$favoriteGroup->getId();
        $this->title  = $favoriteGroup->getFavoriteName();

        foreach ($channels as $channel){
            if($channel->getTvGenreId()==$favoriteGroup->getId()) array_push($this->items,(int)$channel->getId());
        }
    }


}