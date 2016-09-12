<?php

spl_autoload_register ('autoload');

function autoload ($className) {
    $fileName = str_replace('\\','/',$className ). '.php';
    include  $fileName;
}