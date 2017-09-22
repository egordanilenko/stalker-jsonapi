<?php
namespace Response\vod;

use Model\vod\VodTag;
use Response\ResponseInterface;

class VodTagListResponse implements ResponseInterface
{
    /**
     * @var string name
     */
    public $name;

    /**
     * @var VodTag[]
     */
    public $tags=array();

    /**
     * VodTagListResponse constructor.
     * @param string $name
     * @param VodTag[] $tags
     */
    public function __construct($name, array $tags)
    {
        $this->name = $name;
        $this->tags = $tags;
    }

}