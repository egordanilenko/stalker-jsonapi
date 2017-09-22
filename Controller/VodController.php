<?php
namespace Controller;

use Model\AgeGroup;
use Model\vod\VodCategory;
use Model\vod\VodContent;
use Model\vod\VodContentActor;
use Model\vod\VodContentFileItem;
use Model\vod\VodContentItem;
use Model\vod\VodShortContent;
use Model\vod\VodTag;
use Response\RequestResponse;
use Response\vod\VodContentListResponse;
use Response\vod\VodContentResponse;
use Response\vod\VodTagListResponse;
use Utils\ORM;
use Utils\PoTranslator;


class VodController extends AbstractController {


    private $age_group = array(
        ["id" => 1, "caption" => "0+", "age" => 0],
        ["id" => 2, "caption" => "6+", "age" => 6],
        ["id" => 3, "caption" => "12+", "age" => 12],
        ["id" => 4, "caption" => "14+", "age" => 14],
        ["id" => 5, "caption" => "16+", "age" => 16],
        ["id" => 6, "caption" => "18+", "age" => 18],
        ["id" => 7, "caption" => "21+", "age" => 21],
    );

    public function tagListAction() {
        return new RequestResponse('tag_list',200,new VodTagListResponse("primary",$this->getTagList()));
    }

    public function contentListAction() {

        $start = (int)array_key_exists('start',$this->getUrlParam()) ? $this->getUrlParam()['start'] : 0;
        $limit = (int)array_key_exists('limit',$this->getUrlParam()) ? $this->getUrlParam()['limit'] : 10;
        $search = array_key_exists('search',$this->getUrlParam()) ? $this->getUrlParam()['search'] : null;
        $search_tags = array_key_exists('tags',$this->getUrlParam()) ? explode (',', $this->getUrlParam()['tags']) : [];

        $response = new VodContentListResponse(
            $start,
            $limit,
            $this->getContentsCount($search,$search_tags),
            $search,
            $search_tags,
            $this->getTagList(),
            $this->getCategorys(),
            $this->getAgeGroups(),
            $this->getContents($start,$limit,$search,$search_tags));
        
        return new RequestResponse('content_list',200,$response);
    }


    public function contentAction($id) {

        $id = str_replace(".json","",$id);

        $response = new VodContentResponse(
            $this->getContent((int)$id)
        );

        return new RequestResponse('content',200,$response);
    }


    private function getActorByVideo($video) {

        $array_actors = preg_split('/[\r\n\t\f,;:]+/',$video->actors);

        $actors = [];

        foreach ($array_actors as $index => $actor) {
            array_push($actors, new VodContentActor($index,trim($actor),"Actor"));
        }

        return $actors;
    }


    private function getContent($id) {

        $video = ORM::for_table('video')->find_one($id);

        return new VodContent(
            $video->id,
            $video->name,
            $this->getScreenShot($video->id),
            $video->o_name,
            $video->year,
            $video->description,
            $video->time,
            $this->getAgeGroupByCaption($video->age)->getId(),
            $video->category_id,
            $this->getTagIdsByVideo($video), // TagID[]
            $this->getItemsByVideo($video), // Items[]
            $this->getActorByVideo($video), // Team[] Актёры
            $this->getSeasonByVideo($video), // Children[]
            $this->getAgeGroups()
        );
    }

    private function getTagList() {
        
        $videos = ORM::for_table("video")->find_many();

        $tags_ids = array();

        // TODO: Написать более элегантно
        foreach ($videos as $video) {
            foreach ($this->getTagIdsByVideo($video) as $value){
                array_push($tags_ids,$value);
            }
        }

        $result = ORM::for_table('cat_genre')->where_in('id',$tags_ids)->groupBy("title")->find_many();
        $tags = array();

        foreach ($result as $tag) {
            array_push($tags, new VodTag($tag->id,PoTranslator::getInstance()->translate($tag->title)));
        }

        return $tags;
    }

    private function getAgeGroups() {
        $ages = array();

        foreach ($this->age_group as $group) {
            array_push($ages, new AgeGroup($group["id"],$group["age"],$group["caption"]));
        }
        return $ages;
    }

    private function getTagIdsByVideo($video){
        $tags = array();
        if((int)$video->cat_genre_id_1 !== 0){
            array_push($tags, (int)$video->cat_genre_id_1);
        }
        if((int)$video->cat_genre_id_2 !== 0){
            array_push($tags, (int)$video->cat_genre_id_2);
        }
        if((int)$video->cat_genre_id_3 !== 0){
            array_push($tags, (int)$video->cat_genre_id_3);
        }
        if((int)$video->cat_genre_id_4 !== 0){
            array_push($tags, (int)$video->cat_genre_id_4);
        }

        return $tags;
    }

    private function getItemsByVideo($video) {

        if($video->is_series === "1") return [];

        $result = ORM::for_table('video_series_files')
            ->where('video_id',$video->id)
            ->where('status',1)
            ->where_null('series_id')
            ->find_many();

        $items = array();

        foreach ($result as $video_item){
            array_push($items, new VodContentFileItem($video_item->id, "Смотреть" ,$video_item->url,$video_item->quality));
        }

        return $items;
    }

    private function getItemsBySeries($series) {

        $result = ORM::for_table('video_series_files')
            ->where('series_id',$series->id)
            ->where('status',1)
            ->find_many();

        $items = array();

        foreach ($result as $item){
            array_push($items, new VodContentFileItem($item->id, "Смотреть" ,$item->url,$item->quality));
        }

        return $items;
    }


    private function getSeasonByVideo($video) {

        if($video->is_series === "0") return [];

        $result = ORM::for_table('video_season')
            ->where('video_id',$video->id)
            ->find_many();

        $items = array();

        foreach ($result as $item){
            array_push($items, new VodContentItem((int)$item->id, $item->season_name, [],$this->getSeriesBySeason($item)));
        }

        return $items;
    }


    private function getSeriesBySeason($season) {

        $result = ORM::for_table('video_season_series')
            ->where('season_id',$season->id)
            ->find_many();

        $items = array();

        foreach ($result as $item){
            array_push($items, new VodContentItem($item->id, $item->series_name ,$this->getItemsBySeries($item),[]));
        }

        return $items;
    }

    private function getAgeGroupByCaption($caption) {

        foreach ($this->age_group as $group) {
            if($group["caption"] == $caption){
                return new AgeGroup($group["id"],$group["age"],$group["caption"]);
            }
        }
        return null;
    }

    private function getCategorys() {
        $result = ORM::for_table('media_category')->find_many();

        $categorys = array();

        foreach ($result as $media_category){
            array_push($categorys, new VodCategory($media_category->id,PoTranslator::getInstance()->translate($media_category->category_name)));
        }

        return $categorys;
    }

    private function getContents($start,$limit,$search,$search_tags) {

        if(empty($search)){
            $search = '%';
        } else {
            $search = '%'.$search.'%';
        }

        $videos = ORM::for_table('video')->where_like('name', $search) ->where('status',1)->limit($limit)->offset($start);

        // Обработка тэгов
        if(!empty($search_tags)){

            $tags_primary = ORM::for_table('cat_genre')->where_in('id', $search_tags)->find_many();

            $tags_title_search = array();

            // Достаём title этих тэгов
            foreach ($tags_primary as $tag_primary){
                array_push($tags_title_search,$tag_primary->title);
            }

            // Ищем все тэги с нужными title
            $tags_for_search = ORM::for_table('cat_genre')->where_in('title', $tags_title_search)->find_many();

            $tags_id_search = array();

            foreach ($tags_for_search as $tag_for_search){
                array_push($tags_id_search,$tag_for_search->id);
            }

            $tags = ORM::for_table('cat_genre')->select("id")->where_in('title', $search_tags)->find_array();

            $tags_for = array();

            foreach ($tags as $tt) {
                array_push($tags_for,$tt['id']);
            }

            $tags_string = implode(',',$tags_id_search);

            $videos->where_raw('(`cat_genre_id_1` IN ('.$tags_string.') OR `cat_genre_id_2` IN ('.$tags_string.') OR `cat_genre_id_3` IN ('.$tags_string.') OR `cat_genre_id_4` IN ('.$tags_string.'))');
        }

        $categorys = array();

        foreach ($videos->find_many() as $video) {
            array_push($categorys, new VodShortContent($video->id,$video->name,$this->getScreenShot($video->id)));
        }

        return $categorys;
    }


    private function getContentsCount($search,$search_tags) {

        if(empty($search)){
            $search = '%';
        } else {
            $search = '%'.$search.'%';
        }

        return ORM::for_table('video')->where('status',1)->where_like('name', $search)->count();
    }

    private function getScreenShot($media_id) {
        $result = ORM::for_table('screenshots')->where('media_id',$media_id)->order_by_asc('video_episodes')->find_many();

        $screemshots = array();

        foreach($result  as $row_content){
            array_push($screemshots, [
                'id' => $row_content->id,
                'name' => $row_content->name,
                'media_id' => $row_content->media_id,
                'video_episodes' => $row_content->video_episodes,
            ]);
        }

        if(empty($screemshots)) return null;

        $host = isset($this->config['stalker_host']) ? 'http://'.$this->config['stalker_host']: 'http://'.$_SERVER['SERVER_NAME'];
        $filextension = pathinfo($screemshots[0]['name']);
        return $host.'/stalker_portal/screenshots/' . ceil(intval(str_replace('.jpg', '', $screemshots[0]['id'])) / 100) .'/'. $screemshots[0]['id']. '.'.$filextension['extension'];
    }

}