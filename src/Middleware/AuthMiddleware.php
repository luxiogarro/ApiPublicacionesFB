<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use PDO;

class AuthMiddleware {
    public static function validate() {
        $headers = Request::getHeaders();
        $apiKey = $headers['X-API-KEY'] ?? $headers['x-api-key'] ?? null;

        if (!$apiKey) {
            Response::error("API Key requerida (X-API-KEY)", 401);
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, nombre FROM clientes WHERE api_key = ?");
        $stmt->execute([$apiKey]);
        $cliente = $stmt->fetch();

        if (!$cliente) {
            Response::error("API Key inválida", 403);
        }

        return $cliente;
    }
}
