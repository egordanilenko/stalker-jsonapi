<?php


namespace Controller;


use Exception\DeviceApiAuthenticationRequiredException;
use Exception\DeviceApiException;
use Exception\DeviceApiNotFoundException;
use Model\Request;
use Response\ErrorResponse;
use Response\JsonResponse;
use Response\RequestResponse;
use Utils\Inflector;

class Router
{

    private $argumentMapper = array(
        'epg'=>array(0=>'channel_id',1=>'date')
    );

    /**
     * @var Request
     */
    private $request;

    /**
     * @var DeviceApiController
     */
    private $deviceApiController;

    /**
     * @var string
     */
    private $method;

    private $command;

    public function __construct(Request $request, array $config)
    {
        $this->request = $request;
        $requestType = strtolower($_SERVER['REQUEST_METHOD']);

        $this->deviceApiController = new DeviceApiController($request, $config);
        $path = substr($request->getPath(),strlen('/json/'));
        $path=str_replace('.json','',$path);
        $array = explode('/',$path);
        $method = Inflector::camelize(array_shift($array));
        $this->command = Inflector::tableize($method);
        $gets = array();
        if(array_key_exists($method,$this->argumentMapper)){
            foreach($array as $key=>$value){
                $gets[$this->argumentMapper[$method][$key]]=$value;
            }
            if(count($this->argumentMapper[$method])!=count($gets)) throw  new \Exception('Need more arguments for '.$method);
            $request->addGetsArgs($gets);
        }


        $method.='Action';
        if($requestType=='post'){
            $method = $requestType.ucfirst($method);
        }

        if($requestType=='options'){
            $method = 'preflightAction';
        }

        $this->method = $method;
    }

    /**
     * @return JsonResponse
     */
    public function getResponse(){

        try{
            if(!method_exists($this->deviceApiController,$this->method)) throw  new DeviceApiNotFoundException('Command not found',$this->method);

            if($this->deviceApiController->isRegistered() == false && in_array($this->method,$this->deviceApiController->getAnonymousAction())==false) throw  new DeviceApiAuthenticationRequiredException('Need auth');

            return $this->deviceApiController->{$this->method}();
        }catch (DeviceApiException $e){
            $exceptionResponse = new ErrorResponse();
            $requestResponse = new RequestResponse($this->command,$e->getCode(),$exceptionResponse,$e->getMessage());
            return new JsonResponse($requestResponse,$e->getCode());
        }
    }

}