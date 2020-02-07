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
    protected $routes = [
        'GET' => [],
        'POST' => []
    ];
    
    public function __construct(PDO $conn, array $appConfig) {
        $this->conn = $conn;
        $this->appConfig = $appConfig;
    }

    /* ##################################### */
    /* GET AND SET */
    /* ##################################### */

    public function setRoutes(array $routes) {
        $this->routes = $routes;
    }
    public function getRoutes(): array {
        return $this->routes;
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
        if (array_key_exists($method, $this->routes) && array_key_exists($url, $this->routes[$method])) return $this->route($this->routes[$method][$url]);
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
        $routes = $this->routes[$method];
        
        foreach ($routes as $route => $callback) {
            $subPattern = preg_replace('/:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\_\-]+)', $route);
            $pattern = '@^'.$subPattern.'$@D';
            $matches = array();
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                return $this->route($callback, $matches);
            }
        }
//         throw new Exception('Invalid request');
        return $this->route('app\controllers\Controller@switchFailResponse');
    }
    
}
    