<?php


namespace Model;


class Request
{
    /**
     * @var array
     */
    private $headers  = array();

    /**
     * @var string
     */
    private $content;
    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $get;

    /**
     * @var array
     */
    private $post;


    public function __construct(array  $get, array $post,array $headers, $content, $path)
    {
        $this->get = $get;
        $this->post = $post;
        $this->headers = $headers;
        $this->content = $content;
        $this->path = $path;
    }


    public function addGetsArgs(array $array){
        $this->get=array_merge($this->get,$array);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }


    public function getGetParam($key){
        return array_key_exists($key,$this->get)? $this->get[$key]:null;
    }
    public function getPostParam($key){
        return array_key_exists($key,$this->post)? $this->post[$key]:null;
    }
    public function getHeaderParam($param){
        foreach ($this->headers as $key => $value){
            if(strtolower($param)==strtolower($key)) return $value;
        }
        return null;
    }


    public function toLoggerMessage(){
        return array(
            'path'    => $this->path,
            'headers' => $this->headers,
            'content' => $this->content,
            'args'    => array_merge($this->get,$this->post)

        );
    }


}