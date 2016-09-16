<?php
use Model\Request;
use Controller\Router;
use Utils\Database;
use Utils\Logger;
use Response\ErrorResponse;
use Response\JsonResponse;

set_error_handler('error_handler');


if (!function_exists('http_response_code')) {
    function http_response_code($code = NULL) {

        if ($code !== NULL) {

            switch ($code) {
                case 100: $text = 'Continue'; break;
                case 101: $text = 'Switching Protocols'; break;
                case 200: $text = 'OK'; break;
                case 201: $text = 'Created'; break;
                case 202: $text = 'Accepted'; break;
                case 203: $text = 'Non-Authoritative Information'; break;
                case 204: $text = 'No Content'; break;
                case 205: $text = 'Reset Content'; break;
                case 206: $text = 'Partial Content'; break;
                case 300: $text = 'Multiple Choices'; break;
                case 301: $text = 'Moved Permanently'; break;
                case 302: $text = 'Moved Temporarily'; break;
                case 303: $text = 'See Other'; break;
                case 304: $text = 'Not Modified'; break;
                case 305: $text = 'Use Proxy'; break;
                case 400: $text = 'Bad Request'; break;
                case 401: $text = 'Unauthorized'; break;
                case 402: $text = 'Payment Required'; break;
                case 403: $text = 'Forbidden'; break;
                case 404: $text = 'Not Found'; break;
                case 405: $text = 'Method Not Allowed'; break;
                case 406: $text = 'Not Acceptable'; break;
                case 407: $text = 'Proxy Authentication Required'; break;
                case 408: $text = 'Request Time-out'; break;
                case 409: $text = 'Conflict'; break;
                case 410: $text = 'Gone'; break;
                case 411: $text = 'Length Required'; break;
                case 412: $text = 'Precondition Failed'; break;
                case 413: $text = 'Request Entity Too Large'; break;
                case 414: $text = 'Request-URI Too Large'; break;
                case 415: $text = 'Unsupported Media Type'; break;
                case 500: $text = 'Internal Server Error'; break;
                case 501: $text = 'Not Implemented'; break;
                case 502: $text = 'Bad Gateway'; break;
                case 503: $text = 'Service Unavailable'; break;
                case 504: $text = 'Gateway Time-out'; break;
                case 505: $text = 'HTTP Version not supported'; break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
            }

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

            header($protocol . ' ' . $code . ' ' . $text);

            $GLOBALS['http_response_code'] = $code;

        } else {

            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);

        }

        return $code;

    }
}

function error_handler($number, $string, $file, $line)
{
    throw  new \Exception("Error on ".$line.' in '.$file.': '.$string, $number);
}


try{

    $self_config = '/etc/stalker_jsonapi.ini';
    include_once ('autoload.php');
    //include_once ('config.php');

    $stalker_path='/var/www/stalker_portal/';
    $staler_host= $_SERVER['HTTP_HOST'];

    if(file_exists($self_config)){
        $override = parse_ini_file($self_config);
        if(isset($override['stalker_host'])) $staler_host = $override['stalker_host'];
        if(isset($override['stalker_path'])) $stalker_path = $override['stalker_path'];
    }

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

