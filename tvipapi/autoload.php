<?php

spl_autoload_register ('autoload');

function autoload ($className) {
    $fileName = __DIR__.'/../'.str_replace('\\','/',$className ). '.php';
    if(!file_exists($fileName)) throw new \Exception('Class '.$className.' not found in path: '.$fileName);
    include  $fileName;
}