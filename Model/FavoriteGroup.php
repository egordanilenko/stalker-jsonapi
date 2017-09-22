<?php


namespace Model;


use Utils\PoTranslator;

class FavoriteGroup extends ActiveRecord
{
    protected $_table='tv_genre';

    /**
     * @var string
     */
    protected $title;

    /**
     * @var int
     */
    protected $number;

    public function getFavoriteName(){
        return PoTranslator::getInstance()->translate($this->title);
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return (int)$this->number;
    }



}