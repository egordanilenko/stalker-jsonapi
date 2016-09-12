<?php


namespace Response;


class RequestResponse{
    /**
     * @var string
     */
    public $method;

    /**
     * @var int
     */
    public $status = 200;

    /**
     * @var string;
     */
    public $text;

    /**
     * @var object
     */
    public $response;

    /**
     * RequestResponse constructor.
     * @param string $method
     * @param int $status
     * @param ResponseInterface $response
     * @param string $text
     */
    public function __construct($method, $status, ResponseInterface $response,$text = null)
    {
        $this->method = $method;
        $this->status = $status;
        $this->text = $text;
        $this->response = $response;
    }


}
