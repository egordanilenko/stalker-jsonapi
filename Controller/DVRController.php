<?php
namespace Controller;

use Exception\DeviceApiException;
use Exception\ErrorException;
use Model\Channel;
use Model\Request;
use Utils\HlsSreamer;
use Utils\ORM;


class DVRController {

    private $config = array();
    private $request;
    


    public function __construct(Request $request, array $config)
    {
        $this->config = $config;
        $this->request = $request;
    }

    public function timeShiftAction($channel_id, $time) {

        $session_key = "X-Playback-Session-Id";

        if(!isset(getallheaders()[$session_key])) throw new ErrorException("Not found header: X-Playback-Session-Id",500);

        if(isset(getallheaders()[$session_key])) session_id(getallheaders()[$session_key]);

        session_start(['gc_maxlifetime' => 60]);

        $hlsStreamer = new HlsSreamer($channel_id);

        try {
            $segments = $hlsStreamer->getSegmentsByTime($time);
        }catch (\Exception $e){
            throw new ErrorException($e->getMessage(),404);
        }


        if(!array_key_exists("time",$_SESSION)) {
            $_SESSION["time"] = time();
            $_SESSION["segment"] =  end($segments);
            $count = 0;
        } else {
            $old_time = (int)$_SESSION["time"];
            $current_time = time();
            $time += ($current_time - $old_time);
            $segmentsr = $hlsStreamer->getSegmentsByTime($time);
            $first_segment = $_SESSION["segment"];
            $current_segmant = end($segmentsr);
            $count = ($current_segmant->getEndTime() - $first_segment->getEndTime()) / 10;
        }
        reset($segments);

        return $this->render('hls_playlist_m3u8.php', [
            'segments' => $hlsStreamer->getSegmentsByTime($time),
            'count' => $count
        ]);
    }

    private function render($file, $variables = array())
    {
        extract($variables);

        ob_start();
        include '../View/' . $file;
        $renderedView = ob_get_clean();

        return $renderedView;
    }
}