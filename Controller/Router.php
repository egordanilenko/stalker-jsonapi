<?php


namespace Controller;

use Exception\DeviceApiAuthenticationRequiredException;
use Model\Request;
use Response\ErrorResponse;
use Response\JsonResponse;
use Response\RequestResponse;
use Utils\Inflector;

/*
 * Based on https://github.com/bit55/litero
 */
class Router
{

    private $argumentMapper = array(
        'epg'=>array(0=>'channel_id',1=>'date'),
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

    /**
     * @var string
     */
    private $command;

    protected $routes = [];

    protected $requestUri;

    protected $requestMethod;

    protected $requestHandler;

    protected $params = [];

    protected $placeholders = [
        ':seg' => '([^\/]+)',
        ':num'  => '([0-9]+)',
        ':any'  => '(.+)'
    ];

    private $config;

    public function __construct($uri,Request $request, array $config = [])
    {
        $this->request = $request;
        $this->config = $config;
        $requestType = strtolower($_SERVER['REQUEST_METHOD']);

        $this->requestUri = $uri;
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];

        $this->deviceApiController = new DeviceApiController($request, $config);
        $path = substr($request->getPath(),strlen('/json/'));
        $path=str_replace('.json','',$path);
        $array = explode('/',$path);
        $method = Inflector::camelize(array_shift($array));
        $this->command = Inflector::tableize($method);
        $gets = array();
        /*if(array_key_exists($method,$this->argumentMapper)){
            foreach($array as $key=>$value){
                $gets[$this->argumentMapper[$method][$key]]=$value;
            }
            if(count($this->argumentMapper[$method])!=count($gets)) throw  new \Exception('Need more arguments for '.$method);
            $request->addGetsArgs($gets);
        }*/


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
        
        if ($this->isFound()) {

           if($this->deviceApiController->isRegistered() == false && in_array($this->getRequestHandler(),$this->deviceApiController->getAnonymousAction())==false) throw  new DeviceApiAuthenticationRequiredException('Need auth');

            $this->executeHandler(
                $this->getRequestHandler(),
                $this->getParams()
            );
        }
        else {
            // Simple "Not found" handler
            $requestResponse = new ErrorResponse(404,'Not found');
            return new JsonResponse($requestResponse,404);
        }

       /* try{
            if(!method_exists($this->deviceApiController,$this->method)) throw  new DeviceApiNotFoundException('Command not found',$this->method);

            if($this->deviceApiController->isRegistered() == false && in_array($this->method,$this->deviceApiController->getAnonymousAction())==false) throw  new DeviceApiAuthenticationRequiredException('Need auth');

            return $this->deviceApiController->{$this->method}();
        }catch (DeviceApiException $e){
            Logger::log($e);
            $requestResponse = new ErrorResponse($e->getCode(),$e->getMessage());
            return new JsonResponse($requestResponse,$e->getCode());
        }*/
    }

    /**
     * Factory method construct Router from global vars.
     * @return Router
     */
    public static function fromGlobals(Request $request, $conf)
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        return new static($uri, $request,$conf);
    }
    /**
     * Current processed URI.
     * @return string
     */
    public function getRequestUri()
    {
        return $this->requestUri; // ?: '/';
    }

    /**
     * Request method.
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    /**
     * Get Request handler.
     * @return string|callable
     */
    public function getRequestHandler()
    {
        return $this->requestHandler;
    }

    /**
     * Set Request handler.
     * @param $handler string|callable
     */
    public function setRequestHandler($handler)
    {
        $this->requestHandler = $handler;
    }

    /**
     * Request wildcard params.
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Add route rule.
     *
     * @param string|array $route A URI route string or array
     * @param mixed $handler Any callable or string with controller classname and action method like "ControllerClass@actionMethod"
     */
    public function add($route, $handler = null)
    {
        if ($handler !== null && !is_array($route)) {
            $route = array($route => $handler);
        }
        $this->routes = array_merge($this->routes, $route);
        return $this;
    }
    /**
     * Process requested URI.
     * @return bool
     */
    public function isFound()
    {
        $uri = $this->getRequestUri();

        // if URI equals to route
        if (isset($this->routes[$uri])) {
            $this->requestHandler = $this->routes[$uri];
            return true;
        }

        $find    = array_keys($this->placeholders);
        $replace = array_values($this->placeholders);
        foreach ($this->routes as $route => $handler) {
            // Replace wildcards by regex
            if (strpos($route, ':') !== false) {
                $route = str_replace($find, $replace, $route);
            }
            // Route rule matched
            if (preg_match('#^' . $route . '$#', $uri, $matches)) {
                $this->requestHandler = $handler;
                $this->params = array_slice($matches, 1);
                return true;
            }
        }

        return false;
    }
    /**
     * Execute Request Handler.
     *
     * @param string|callable $handler
     * @param array $params
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function executeHandler($handler = null, $params = null)
    {
        if ($handler === null) {
            throw new \InvalidArgumentException(
                'Request handler not setted out. Please check '.__CLASS__.'::isFound() first'
            );
        }

        // execute action in callable
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }
        // execute action in controllers
        if (strpos($handler, '@')) {
            $ca = explode('@', $handler);
            $controllerName = $ca[0];
            $action = $ca[1];
            if (class_exists($controllerName)) {
                $controller = new $controllerName($this->request, $this->config);
            } else {
                throw new \RuntimeException("Controller class '{$controllerName}' not found");
            }
            if (!method_exists($controller, $action)) {
                throw new \RuntimeException("Method '{$controllerName}::{$action}' not found");
            }

            if($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action =  'post'.ucfirst($action);
            }

            $response = call_user_func_array(array($controller, $action), $params);

            self::generateResponse($response,$response->status);
        }
    }

    public static function generateResponse(RequestResponse $requestResponse,$code=200){

        $headers = array(
            'Access-Control-Allow-Methods'     => 'GET, POST, OPTIONS',
            'Access-Control-Request-Headers'   => 'Accept, X-Requested-With',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Headers'     => 'Mac-Address, Device-Type, Device-Version, Device-Os, Device-Firmware, X-Auth-Token, Auth-Token'
        );

        if(isset($_SERVER['HTTP_ORIGIN'])){
            $headers = array_merge(
                $headers,
                array(
                    'Access-Control-Allow-Origin'=>$_SERVER['HTTP_ORIGIN']
                )
            );
        }

        $response = new  JsonResponse($requestResponse,$code,$headers);
        $response->renderJson();
    }

}