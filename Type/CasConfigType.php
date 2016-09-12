<?php


namespace Type;


use Model\CasConfig;

class CasConfigType
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $type;

    /**
     * @var \stdClass
     */
    public $options;

    public function __construct(CasConfig $casConfig)
    {
        $this->id=$casConfig->getId();
        $this->options = new \stdClass();
        $this->type = $casConfig->getCasType()->getName();
        foreach($casConfig->getOptions() as $casConfigOption){
            $this->options->{$casConfigOption->getName()}=$casConfigOption->getValue();

        }
    }


}