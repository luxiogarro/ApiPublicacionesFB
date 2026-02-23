<?php
// admin/delete_publicacion.php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

checkAuth();
header('Content-Type: application/json');

use App\Core\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $db = Database::getInstance()->getConnection();
    
    try {
        // 1. Obtener archivos físicos
        $stmt = $db->prepare("SELECT ruta_archivo FROM adjuntos WHERE publicacion_id = ?");
        $stmt->execute([$id]);
        $adjuntos = $stmt->fetchAll();
        
        // 2. Borrar archivos del disco
        foreach ($adjuntos as $a) {
            $filePath = __DIR__ . '/../uploads/' . $a['ruta_archivo'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
        
        // 3. Borrar la publicación (Las FK ON DELETE CASCADE borrarán las entradas de adjuntos en DB)
        $stmtDel = $db->prepare("DELETE FROM publicaciones WHERE id = ?");
        $stmtDel->execute([$id]);
        
        echo json_encode(['success' => true]);
        
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
