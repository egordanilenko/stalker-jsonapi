<?php
use Model\Request;
use Controller\Router;
use Utils\Database;
use Response\JsonResponse;

set_error_handler('error_handler');

include_once ('../autoload.php');
include_once ('config.php');


function error_handler($number, $string, $file, $line)
{
    throw  new \Exception("Error on ".$line.' in '.$file.': '.$string,$number);
}


try{


    if(file_exists($stalker_config_path)) {
        $conf = parse_ini_file($stalker_config_path);
        $test = $conf;
    }else{
        throw  new \Exception('File '.$stalker_config_path.' must be present');
    }
    $mysqli = new mysqli(isset($conf['mysql_host']) ? $conf['mysql_host']:'localhost' , $conf["mysql_user"], $conf["mysql_pass"], $conf["db_name"]);
    $mysqli->set_charset("utf8");

    Database::getInstance()->setMysqli($mysqli);


    $path = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['BASE']));
    $request = new Request($_GET,$_POST,getallheaders(),file_get_contents('php://input'),$path);

    if (isset($stalker_host)) $conf = array_merge($conf,array('stalker_host'=>$stalker_host));
    $router = new Router($request,$conf);
    $jsonResponse = $router->getResponse();
}catch (Exception $e){
    $jsonResponse = new JsonResponse($e->getMessage(),$e->getCode());
}

$jsonResponse->renderJson();