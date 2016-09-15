<?php


namespace Utils;


class Logger
{
    private static $logFile = '/var/log/stalker_jsonapi.log';

    public static function log(\Exception $e){
        $message = '('.get_class($e).'): '.$e->getMessage().'. code: '.$e->getCode();
        self::write($message);
    }


    public static function write($message){
        $message = '['.date('Y-m-d H:i:s').'] '.$message.PHP_EOL;
        if(file_exists(self::$logFile) && is_writable(self::$logFile)) file_put_contents(self::$logFile,$message,FILE_APPEND);
    }
}