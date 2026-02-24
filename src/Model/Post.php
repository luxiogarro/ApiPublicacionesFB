<?php
namespace App\Model;

use App\Core\Database;
use PDO;

class Post {
    public static function create($data) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO publicaciones (usuario_id, cliente_id, titulo, contenido, video_url, tipo, fijada, fijada_hasta) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['usuario_id'],
            $data['cliente_id'],
            $data['titulo'] ?? '',
            $data['contenido'],
            $data['video_url'] ?? null,
            $data['tipo'] ?? 'texto',
            $data['fijada'] ?? 0,
            $data['fijada_hasta'] ?? null
        ]);
        return $db->lastInsertId();
    }

    public static function addAttachment($post_id, $file_data, $es_portada = 0) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO adjuntos (publicacion_id, ruta_archivo, tipo_archivo, nombre_original, es_portada) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $post_id,
            $file_data['ruta'],
            $file_data['tipo'],
            $file_data['nombre'],
            $es_portada
        ]);
    }

    public static function getAllByClient($cliente_id, $limit = null, $offset = 0, $filters = []) {
        $db = Database::getInstance()->getConnection();
        
        $where = "p.cliente_id = ?";
        $params = [$cliente_id];

        if (!empty($filters['search'])) {
            $where .= " AND (p.titulo LIKE ? OR p.contenido LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $where .= " AND p.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where .= " AND p.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $sql = "SELECT p.*, u.nombre as usuario_nombre, u.avatar as usuario_avatar,
                (p.fijada = 1 AND (p.fijada_hasta IS NULL OR p.fijada_hasta >= NOW())) AS es_fijada_activa
                FROM publicaciones p 
                JOIN usuarios u ON p.usuario_id = u.id 
                WHERE $where 
                ORDER BY es_fijada_activa DESC, p.created_at DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll();

        foreach ($posts as &$post) {
            $post['adjuntos'] = self::getAttachments($post['id']);
        }
        return $posts;
    }

    public static function getAllGlobal($limit = null, $offset = 0, $filters = []) {
        $db = Database::getInstance()->getConnection();
        
        $where = "1=1";
        $params = [];

        if (!empty($filters['cliente_id'])) {
            $where .= " AND p.cliente_id = ?";
            $params[] = $filters['cliente_id'];
        }
        if (!empty($filters['search'])) {
            $where .= " AND (p.titulo LIKE ? OR p.contenido LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $where .= " AND p.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where .= " AND p.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $sql = "SELECT p.*, u.nombre as usuario_nombre, u.avatar as usuario_avatar, c.nombre as cliente_nombre,
                (p.fijada = 1 AND (p.fijada_hasta IS NULL OR p.fijada_hasta >= NOW())) AS es_fijada_activa
                FROM publicaciones p 
                JOIN usuarios u ON p.usuario_id = u.id 
                JOIN clientes c ON p.cliente_id = c.id
                WHERE $where
                ORDER BY es_fijada_activa DESC, p.created_at DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll();

        foreach ($posts as &$post) {
            $post['adjuntos'] = self::getAttachments($post['id']);
        }
        return $posts;
    }

    public static function update($id, $cliente_id, $data) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE publicaciones SET titulo = ?, contenido = ?, video_url = ?, fijada = ?, fijada_hasta = ? WHERE id = ? AND cliente_id = ?");
        return $stmt->execute([
            $data['titulo'] ?? '',
            $data['contenido'],
            $data['video_url'] ?? null,
            $data['fijada'] ?? 0,
            $data['fijada_hasta'] ?? null,
            $id,
            $cliente_id
        ]);
    }

    public static function delete($id, $cliente_id) {
        $db = Database::getInstance()->getConnection();
        
        // Primero eliminar adjuntos (opcional si hay ON DELETE CASCADE, pero mejor ser explícito)
        // Aunque generalmente se prefiere que la DB lo maneje.
        
        $stmt = $db->prepare("DELETE FROM publicaciones WHERE id = ? AND cliente_id = ?");
        $stmt->execute([$id, $cliente_id]);
        return $stmt->rowCount() > 0;
    }

    private static function getAttachments($post_id) {
        $db = Database::getInstance()->getConnection();
        $stmtAdj = $db->prepare("SELECT * FROM adjuntos WHERE publicacion_id = ?");
        $stmtAdj->execute([$post_id]);
        return $stmtAdj->fetchAll();
    }
}
