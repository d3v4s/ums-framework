<?php
session_start();
use app\db\DbFactory;
use app\core\Router;
chdir(dirname(__DIR__));
require_once getcwd().'/autoload.php';
require_once getcwd().'/helpers/functions.php';
$data = require getcwd().'/config/db.config.php';
// $appConfig = require getcwd().'/config/app.config.php';

try {
    $conn = DbFactory::create($data)->getConn();
    $router = new Router($conn);
    $router->setRoutes(getConfig('routes'));
    $controller = $router->dispatch();
    $controller->display();
//     var_dump($router->getRoutes());
//     $controller->dispatch();
//     $controller->display();
} catch (Exception $e) {
    echo $e->getMessage().' -- code: '.$e->getCode();
}


// $controller->show(1);
