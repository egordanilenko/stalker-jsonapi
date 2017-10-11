<?php


namespace Response;



class M3u8Response
{
    private $content;
    private $code;
    private $headers=array();

    /**
     * JsonResponse constructor.
     * @param $content
     * @param $code int
     * @param $headers array
     */
    public function __construct($content, $code=200, array $headers=array())
    {
        $this->content = $content;
        $this->code = $code;
        $this->headers = $headers;
    }

    public function renderJson(){
        http_response_code($this->code);
        header('Content-type: audio/x-mpegurl');

        foreach ($this->headers as $id => $header){
            $header  =$id.': '.$header;
            header($header);
        }

        echo $this->content;

    }

    public function toLoggerMessage(){
        return array(
            'headers' => $this->headers,
            'content' => $this->content,
            'code'    => $this->code
        );
    }
}