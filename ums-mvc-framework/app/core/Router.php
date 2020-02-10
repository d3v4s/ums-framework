<?php
namespace app\core;

use \PDO;
use app\controllers\Controller;

/**
 * Class Router to manage the routing of request
 * @author Andrea Serra (DevAS) https://devas.info
 */
class Router {
    protected $conn;
    protected $appConfig;
    static protected $routes = [
        'GET' => [],
        'POST' => []
    ];
    
    public function __construct(PDO $conn, array $appConfig) {
        $this->conn = $conn;
        $this->appConfig = $appConfig;
    }

    /* static function to get url from routes by class and method */
    static public function getRoute(string $class, string $method, string $reqMethod='GET'): string {
        return isset(Router::$routes[$reqMethod]) ? '/'.array_search("$class@$method", Router::$routes[$reqMethod]) : '/';
    }

    /* ##################################### */
    /* GET AND SET */
    /* ##################################### */

    public function setRoutes(array $routes) {
        Router::$routes = $routes;
    }
    public function getRoutes(): array {
        return Router::$routes;
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function handler to dispatch the request */
    public function dispatch() {
        /* get data of request */
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $url = trim($url, '/');
        /* get request method */
        $method = $_SERVER['REQUEST_METHOD'];
        /* check if request is on routes, then return the controller  */
        if (array_key_exists($method, Router::$routes) && array_key_exists($url, Router::$routes[$method])) return $this->route(Router::$routes[$method][$url]);
        /* else try to process a request */
        return $this->processQueue($url, $method);
    }

    /* ##################################### */
    /* PROTECTED FUNCTIONS */
    /* ##################################### */

    /* function to get controller from routes */ 
    protected function route($callback, array $matches = []) {
        /* check if is callable, then call it and return */
        if (is_callable($callback)) return call_user_func_array($callback, $matches);
        /* else get class and method */
        $tokens = explode('@', $callback);
        $class = new $tokens[0]($this->conn, $this->appConfig);
        $method = $tokens[1];
        /* if is valid calss and method, then call it */
        if (method_exists($class, $method)) call_user_func_array([$class, $method], $matches);
        /* return class */
        return $class;
    }

    /* function to process a request */
    protected function processQueue(string $uri, string $method='GET'): Controller {
        if (isset(Router::$routes[$method])) {
            $routes = Router::$routes[$method];
            
            foreach ($routes as $route => $callback) {
                $subPattern = preg_replace('/:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\_\-]+)', $route);
                $pattern = '@^'.$subPattern.'$@D';
                $matches = array();
                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches);
                    return $this->route($callback, $matches);
                }
            }
        }
        return $this->route('app\controllers\Controller@switchFailResponse');
    }
    
}
    