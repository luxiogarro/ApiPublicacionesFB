<?php
// admin/delete_adjunto.php
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
        $stmt = $db->prepare("SELECT ruta_archivo FROM adjuntos WHERE id = ?");
        $stmt->execute([$id]);
        $a = $stmt->fetch();
        
        if ($a) {
            $filePath = __DIR__ . '/../uploads/' . $a['ruta_archivo'];
            if (file_exists($filePath)) { @unlink($filePath); }
            
            $db->prepare("DELETE FROM adjuntos WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Not found']);
        }
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid']);
}
