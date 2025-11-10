<?php

class Router {
    private $routes = [];

    public function addRoute($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function resolve($requestMethod, $requestPath) {
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $route['path'] === $requestPath) {
                return [
                    'controller' => $route['controller'],
                    'action' => $route['action']
                ];
            }
        }
        return null; // No route found
    }

    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove trailing slash except for root
        if ($requestPath !== '/' && substr($requestPath, -1) === '/') {
            $requestPath = rtrim($requestPath, '/');
        }

        $route = $this->resolve($requestMethod, $requestPath);

        if ($route) {
            $controllerName = $route['controller'];
            $actionName = $route['action'];

            // Instantiate controller and call action
            if (class_exists($controllerName)) {
                $controller = new $controllerName();
                if (method_exists($controller, $actionName)) {
                    $controller->$actionName();
                } else {
                    $this->handle404("Action '$actionName' not found in controller '$controllerName'");
                }
            } else {
                $this->handle404("Controller '$controllerName' not found");
            }
        } else {
            $this->handle404("Route not found for $requestMethod $requestPath");
        }
    }

    private function handle404($message = "Page not found") {
        http_response_code(404);
        echo "<h1>404 - Not Found</h1>";
        echo "<p>$message</p>";
    }
}