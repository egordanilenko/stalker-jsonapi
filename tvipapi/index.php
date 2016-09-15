<?php
use Model\Request;
use Controller\Router;
use Utils\Database;
use Utils\Logger;
use Response\ErrorResponse;
use Response\JsonResponse;

set_error_handler('error_handler');


function error_handler($number, $string, $file, $line)
{
    throw  new \Exception("Error on ".$line.' in '.$file.': '.$string, $number);
}


try{

    include_once ('autoload.php');
    include_once ('config.php');

    $conf = array();
    if(!file_exists($stalker_path)) throw  new Exception("Work directory of stalker portal is not exist",500);
    $config_path = $stalker_path.'/server/config.ini';
    $custom_path = $stalker_path.'/server/custom.ini';

    if(file_exists($config_path)) {
        $conf   = parse_ini_file($config_path);
    }else{
        throw  new \Exception('File '.$config_path.' must be present', 500);
    }

    if(file_exists($custom_path)){
        $custom = parse_ini_file($custom_path);
        $conf = array_merge($conf,$custom);
    }
    $conf = array_merge($conf,array('stalker_path'=>$stalker_path));

    $mysqli = new mysqli(isset($conf['mysql_host']) ? $conf['mysql_host']:'localhost' , $conf["mysql_user"], $conf["mysql_pass"], $conf["db_name"]);
    $mysqli->set_charset("utf8");

    Database::getInstance()->setMysqli($mysqli);

    $path = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['BASE']));
    $request = new Request($_GET,$_POST,getallheaders(),file_get_contents('php://input'),$path);

    if (isset($stalker_host)) $conf = array_merge($conf,array('stalker_host'=>$stalker_host,'debug'=>true));

    $router = new Router($request,$conf);
    $jsonResponse = $router->getResponse();

}catch (Exception $e){

    Logger::log($e);
    $jsonResponse = new JsonResponse(new ErrorResponse($e->getCode(),$e->getMessage()), $e->getCode());
}

$jsonResponse->renderJson();
//
//$log = array('request'=>$request->toLoggerMessage(), 'response'=>$jsonResponse->toLoggerMessage());
//Logger::write(json_encode($log,JSON_PRETTY_PRINT));