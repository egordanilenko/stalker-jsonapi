<?php

namespace Type;

use Model\AgeGroup;
class AgeGroupType
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $age;

    /**
     * @var string
     */
    public $caption;

    /**
     * AgeGroupType constructor.
     * @param AgeGroup $ageGroup
     */
    public function __construct(AgeGroup $ageGroup)
    {
        $this->id = $ageGroup->getId();
        $this->age = $ageGroup->getAge();
        $this->caption = $ageGroup->getCaption();
    }


}