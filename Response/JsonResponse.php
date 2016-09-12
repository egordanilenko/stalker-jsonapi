<?php


namespace Response;



class JsonResponse
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
        header('Content-Type: application/json');

        foreach ($this->headers as $id => $header){
            $header  =$id.': '.$header;
            header($header);
        }

        echo json_encode($this->content);

    }
}