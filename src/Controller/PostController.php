<?php
namespace App\Controller;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Model\Post;
use App\Model\User;

class PostController {
    public function index($params, $data, $query) {
        $cliente = $this->authenticate();
        
        $limit = isset($query['limit']) ? (int)$query['limit'] : null;
        $offset = isset($query['offset']) ? (int)$query['offset'] : 0;
        
        $filters = [
            'search' => $query['search'] ?? null,
            'date_from' => $query['date_from'] ?? null,
            'date_to' => $query['date_to'] ?? null
        ];

        $posts = Post::getAllByClient($cliente['id'], $limit, $offset, $filters);
        Response::json($posts);
    }

    public function store() {
        $cliente = AuthMiddleware::validate();
        $data = Request::getBody();

        if (!isset($data['usuario_nombre']) || !isset($data['contenido'])) {
            Response::error("Datos incompletos (usuario_nombre, contenido)", 400);
        }

        // Obtener o crear usuario
        $usuario_id = User::getOrCreate($cliente['id'], [
            'nombre' => $data['usuario_nombre'],
            'email' => $data['usuario_email'] ?? null,
            'external_id' => $data['usuario_external_id'] ?? null,
            'avatar' => $data['usuario_avatar'] ?? null
        ]);

        $post_id = Post::create([
            'usuario_id' => $usuario_id,
            'cliente_id' => $cliente['id'],
            'titulo' => $data['titulo'] ?? '',
            'contenido' => $data['contenido'],
            'video_url' => $data['video_url'] ?? null,
            'tipo' => $data['tipo'] ?? 'texto'
        ]);

        // Procesar archivos adjuntos si existen
        if (!empty($_FILES['archivos'])) {
            $this->handleFileUploads($post_id);
        }

        Response::json(['message' => 'Publicación creada con éxito', 'post_id' => $post_id], 201);
    }

    private function handleFileUploads($post_id) {
        $files = $_FILES['archivos'];
        $uploaded_count = count($files['name']);

        for ($i = 0; $i < $uploaded_count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                // Intentar optimizar si es imagen
                $optimizedName = \App\Core\ImageOptimizer::optimize($files['tmp_name'][$i], $files['name'][$i]);
                
                if ($optimizedName) {
                    $filename = $optimizedName;
                    $fileType = 'image/webp';
                } else {
                    // Fallback para otros archivos o si falla la optimización
                    $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $ext;
                    move_uploaded_file($files['tmp_name'][$i], UPLOAD_DIR . $filename);
                    $fileType = $files['type'][$i];
                }

                Post::addAttachment($post_id, [
                    'ruta' => $filename,
                    'tipo' => $fileType,
                    'nombre' => $files['name'][$i]
                ]);
            }
        }
    }
}
