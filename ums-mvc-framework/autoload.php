<?php
function autoloadClass($className) {
    $link = $_SERVER['DOCUMENT_ROOT'].'/../'.str_replace('\\', '/', $className).'.php';
    if (file_exists($link)) require_once $link;
    else throw new Exception("Class $className not found");
}
spl_autoload_register('autoloadClass');
