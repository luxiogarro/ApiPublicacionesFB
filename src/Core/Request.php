<?php
namespace App\Core;

class Request {
    public static function getMethod() {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    public static function getPath() {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $base = '/ApiPublicacionesFB'; // Ajustar si es necesario
        
        $path = str_replace($base, '', $path);
        $position = strpos($path, '?');
        
        if ($position === false) {
            return $path;
        }
        
        return substr($path, 0, $position);
    }

    public static function getBody() {
        $body = [];
        
        if (self::getMethod() === 'GET') {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        
        if (self::getMethod() === 'POST') {
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
            
            // Si es JSON
            $input = json_decode(file_get_contents('php://input'), true);
            if ($input) {
                foreach ($input as $key => $value) {
                    $body[$key] = $value;
                }
            }
        }
        
        return $body;
    }

    public static function getHeaders() {
        return getallheaders();
    }
}
