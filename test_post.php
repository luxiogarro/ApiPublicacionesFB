<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/autoload.php';

use App\Core\Database;
use App\Model\Post;
use App\Model\User;

try {
    $db = Database::getInstance()->getConnection();
    echo "1. DB Connected\n";

    // Need a valid client
    $stmt = $db->query("SELECT id FROM clientes LIMIT 1");
    $cliente = $stmt->fetch();
    if (!$cliente) {
        $db->query("INSERT INTO clientes (nombre, api_key) VALUES ('Test Client', 'test_key_123')");
        $cliente_id = $db->lastInsertId();
    } else {
        $cliente_id = $cliente['id'];
    }
    echo "2. Client ID: $cliente_id\n";

    $usuario_id = User::getOrCreate($cliente_id, [
        'nombre' => 'Test User',
        'external_id' => 'test_123'
    ]);
    echo "3. User ID: $usuario_id\n";

    $post_id = Post::create([
        'usuario_id' => $usuario_id,
        'cliente_id' => $cliente_id,
        'titulo' => 'Test Post',
        'contenido' => 'Esto es texto de prueba unitaria',
        'tipo' => 'texto',
        'fijada' => 1
    ]);

    echo "4. SUCCESS! Post ID created: $post_id\n";
    
    // Test fetch
    $posts = Post::getAllGlobal(1);
    echo "5. Fetch successful. Found " . count($posts) . " posts\n";

    // Rollback tests
    $db->query("DELETE FROM publicaciones WHERE id = $post_id");
    echo "6. Cleanup complete\n";

} catch (Exception $e) {
    echo "ERROR UNITARIO CAPTURADO:\n";
    echo $e->getMessage() . "\n";
}
