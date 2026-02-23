<?php
namespace App\Model;

use App\Core\Database;
use PDO;

class User {
    public static function getOrCreate($cliente_id, $data) {
        $db = Database::getInstance()->getConnection();
        
        // Buscar por external_id o email
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE cliente_id = ? AND (external_id = ? OR email = ?)");
        $stmt->execute([$cliente_id, $data['external_id'] ?? null, $data['email'] ?? null]);
        $user = $stmt->fetch();

        if ($user) {
            // Actualizar nombre y avatar con los datos más recientes enviados
            $updateStmt = $db->prepare("UPDATE usuarios SET nombre = ?, avatar = ? WHERE id = ?");
            $updateStmt->execute([
                $data['nombre'],
                $data['avatar'] ?? $user['avatar'], // conservar avatar anterior si no se envía uno nuevo
                $user['id']
            ]);
            return $user['id'];
        }

        // Crear si no existe
        $stmt = $db->prepare("INSERT INTO usuarios (cliente_id, external_id, nombre, email, avatar) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $cliente_id,
            $data['external_id'] ?? null,
            $data['nombre'],
            $data['email'] ?? null,
            $data['avatar'] ?? null
        ]);

        return $db->lastInsertId();
    }
}
