<?php
session_start();

use app\db\DbFactory;
use app\core\Router;
use app\controllers\Controller;

chdir(dirname(__DIR__));
require_once getcwd().'/autoload.php';
require_once getcwd().'/helpers/functions.php';
$dbConf = require getcwd().'/config/db.config.php';

try {
    $conn = DbFactory::create($dbConf)->getConn();
    $router = new Router($conn, getConfig());
    $router->setRoutes(getRoutes());
    $controller = $router->dispatch();
    $controller->display();
} catch (Exception $e) {
    $controller = new Controller(NULL, getConfig());
    $controller->showPageError($e);
    $controller->display();
}
