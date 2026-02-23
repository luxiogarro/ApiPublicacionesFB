<?php
// index.php - Punto de entrada de la API

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/autoload.php';

use App\Core\Router;
use App\Core\Response;

$router = new Router();

// Definición de rutas
$router->add('GET', '/publicaciones', [\App\Controller\PostController::class, 'index']);
$router->add('POST', '/publicaciones', [\App\Controller\PostController::class, 'store']);

$router->add('GET', '/', function() {
    Response::json(['message' => 'API de Publicaciones Centralizada activa']);
});

// Resolver ruta
try {
    $router->resolve();
} catch (\Exception $e) {
    Response::json(['error' => $e->getMessage()], 500);
}
