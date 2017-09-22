<?php
namespace Response\vod;

use Model\vod\VodTag;
use Response\ResponseInterface;

class VodContentResponse implements ResponseInterface
{
    public $content;


    public function __construct($content) {
        $this->content = $content;
    }
}