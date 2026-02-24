<?php
namespace App\Core;

class Router {
    protected $routes = [];

    public function add($method, $path, $callback) {
        $path = trim($path, '/');
        // Convertir {param} a una expresión regular capturadora
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = "#^" . $pattern . "$#";

        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'callback' => $callback
        ];
    }

    public function resolve() {
        $method = Request::getMethod();
        $path = trim(Request::getPath(), '/');

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $path, $matches)) {
                // Filtrar solo los grupos capturados por nombre
                $params = array_filter($matches, function($key) {
                    return is_string($key);
                }, ARRAY_FILTER_USE_KEY);

                $callback = $route['callback'];
                
                if (is_array($callback)) {
                    $controller = new $callback[0]();
                    // Pasar parámetros extraídos al método del controlador
                    return call_user_func_array([$controller, $callback[1]], [$params]);
                }
                
                return call_user_func($callback, $params);
            }
        }

        Response::error("Ruta no encontrada: $method $path", 404);
    }
}
