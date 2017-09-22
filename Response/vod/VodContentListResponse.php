<?php
namespace Response\vod;

use Model\vod\VodTag;
use Response\ResponseInterface;

class VodContentListResponse implements ResponseInterface
{
    public $start;
    public $limit;
    public $count;
    public $search;
    public $search_tags;
    public $tags;
    public $categories;
    public $age_groups;
    public $content;


    /**
     * VodContentListResponse constructor.
     * @param string $name
     * @param VodTag[] $tags
     */
    public function __construct($start = 0,
                                $limit = 10,
                                $count = 0,
                                $search = null ,
                                array $search_tags = [],
                                array $tags = [],
                                array $categories = [],
                                array $age_groups = [],
                                array $content = []) {

        $this->start = $start;
        $this->limit = $limit;
        $this->count = $count;
        $this->search = $search;
        $this->search_tags = $search_tags;
        $this->tags = $tags;
        $this->categories = $categories;
        $this->age_groups = $age_groups;
        $this->content = $content;
    }
}