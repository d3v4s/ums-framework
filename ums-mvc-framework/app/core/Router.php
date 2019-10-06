<?php
namespace app\core;

use \PDO;
use \Exception;
use app\controllers\Controller;

class Router {
    protected $conn;
    protected $routes = [
        'GET' => [],
        'POST' => []
    ];
    
    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }
    
    public function setRoutes(array $routes) {
        $this->routes = $routes;
    }
    
    public function getRoutes() {
        return $this->routes;
    }
    
    protected function route($callback, array $matches = []): Controller {
        if (is_callable($callback)) return call_user_func_array($callback, $matches);
        $tokens = explode('@', $callback);
        $class = new $tokens[0]($this->conn);
        $method = $tokens[1];
        if (method_exists($class, $method)) call_user_func_array([$class, $method], $matches);
//         else throw new Exception('Method not found');
        return $class;
    }
    
    protected function processQueue($uri, $method = 'GET'): Controller {
        $routes = $this->routes[$method];
        
        foreach ($routes as $route => $callback) {
//             $nameParams = array();
//             preg_match('/:([a-zA-Z0-9\_\-]+)/', $route, $nameParams);
//             var_dump($nameParams); die;
            $subPattern = preg_replace('/:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\_\-]+)', $route);
            $pattern = '@^'.$subPattern.'$@D';
            $matches = array();
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                return $this->route($callback, $matches);
            }
        }
        throw new Exception('Invalid request');
    }
    
    public function dispatch(): Controller {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $url = trim($url, '/');
        $method =$_SERVER['REQUEST_METHOD'];
        if (array_key_exists($method, $this->routes) && array_key_exists($url, $this->routes[$method]))
            return $this->route($this->routes[$method][$url]);
            
        return $this->processQueue($url, $method);
    }
}
    
    