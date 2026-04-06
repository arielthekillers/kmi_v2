<?php

namespace App\Core;

class Router {
    protected $routes = [];

    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }

    public function dispatch($uri, $method) {
        // Strip query string and trim slashes
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Remove project folder from path if running in subdir (e.g. /kmi/public/login -> /login)
        // Adjust this base path detection as needed
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if (strpos($path, $scriptName) === 0) {
            $path = substr($path, strlen($scriptName));
        }
        $path = '/' . trim($path, '/');
        
        // Strip .php extension for legacy support
        if (substr($path, -4) === '.php') {
            $path = substr($path, 0, -4);
        }

        if ($path === '/index' || $path === '') {
            $path = '/';
        }

        $method = strtoupper($method);

        if (isset($this->routes[$method][$path])) {
            $callback = $this->routes[$method][$path];

            if (is_array($callback)) {
                $controller = new $callback[0]();
                $methodName = $callback[1];
                return $controller->$methodName();
            }

            return call_user_func($callback);
        }

        return "404 Not Found";
    }
}
