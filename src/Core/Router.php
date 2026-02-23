<?php
namespace App\Core;

class Router {
    protected $routes = [];

    public function add($method, $path, $callback) {
        $path = trim($path, '/');
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'callback' => $callback
        ];
    }

    public function resolve() {
        $method = Request::getMethod();
        $path = trim(Request::getPath(), '/');

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                $callback = $route['callback'];
                
                if (is_array($callback)) {
                    $controller = new $callback[0]();
                    return $controller->{$callback[1]}();
                }
                
                return call_user_func($callback);
            }
        }

        Response::error("Ruta no encontrada: $method $path", 404);
    }
}
