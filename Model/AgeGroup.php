<?php


namespace Model;


class AgeGroup
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $age;

    /**
     * @var string
     */
    private $caption;

    /**
     * AgeGroup constructor.
     * @param int $id
     * @param int $age
     * @param string $caption
     */
    public function __construct($id, $age, $caption)
    {
        $this->id=$id;
        $this->age = $age;
        $this->caption = $caption;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

}