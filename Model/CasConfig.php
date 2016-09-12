<?php


namespace Model;


class CasConfig
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var CasType
     */
    private $casType;

    /**
     * @var string
     */
    private $name;

    /**
     * @var  CasConfigOption[]
     */
    private $casOptions=array();

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return CasType
     */
    public function getCasType()
    {
        return $this->casType;
    }

    /**
     * @return CasConfigOption[]
     */
    public function getOptions()
    {
        return $this->casOptions;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}