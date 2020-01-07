<?php
/* start session */
session_start();

use app\db\DbFactory;
use app\core\Router;
use app\controllers\Controller;

/* change work directory */
chdir(dirname(__DIR__));
/* require functions autoload and constants */
require_once getcwd().DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'functions.php';
require_once getPath(getcwd(), 'autoload.php');
require_once getPath(getcwd(), 'const', 'app.const.php');
require_once getPath(getcwd(), 'const', 'config.const.php');
require_once getPath(getcwd(), 'const', 'db.const.php');

/* get database config */
$dbConf = require getPath(getcwd(), 'config', 'db.config.php');

try {
    /* get database connection */
    $conn = DbFactory::create($dbConf)->getConn();
    /* init router and set routes */
    $router = new Router($conn, getConfig());
    $router->setRoutes(getRoutes());
    /* get controller, and if is valid controller then display page */
    $controller = $router->dispatch();
    if (is_subclass_of($controller, 'Controller', FALSE)) $controller->display();
} catch (Exception $e) {
    /* init base controller and show error */
    $controller = new Controller(NULL, getConfig());
    $controller->showPageError($e);
    $controller->display();
}
