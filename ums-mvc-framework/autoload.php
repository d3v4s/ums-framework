<?php
function autoloadClass($className) {
    $fileName = '';
    $namespace = '';
    $namespaceSeparator = '\\';
    $fileExtension = '.php';
    if (false !== ($lastNsPos = \strripos($className, $namespaceSeparator))) {
        $namespace = \substr($className, 0, $lastNsPos);
        $className = \substr($className, $lastNsPos + 1);
        $fileName = \str_replace($namespaceSeparator, DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= \str_replace('_', DIRECTORY_SEPARATOR, $className) . $fileExtension;
    require \getPath(WORK_DIR, [$fileName]);
//     $link = $_SERVER['DOCUMENT_ROOT'].'/../'.str_replace('\\', '/', $className).'.php';
//     if (file_exists($link)) require_once $link;
//     else throw new Exception("Class $className not found");
}
spl_autoload_register('autoloadClass');
