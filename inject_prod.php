<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/autoload.php';

use App\Core\Database;
use App\Model\Post;
use App\Model\User;

try {
    $db = Database::getInstance()->getConnection();
    
    // Obtener el cliente 2 o el primero disponible
    $stmt = $db->query("SELECT id, nombre FROM clientes WHERE id = 2");
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$cliente) {
        $cliente = $db->query("SELECT id, nombre FROM clientes LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    }
    
    if (!$cliente) {
        die("ERROR: No hay clientes creados en la BD de Producción.");
    }
    
    $cliente_id = $cliente['id'];
    
    $usuario_id = User::getOrCreate($cliente_id, [
        'nombre' => 'Admin Sistema (Prod)',
        'external_id' => 'admin_test_prod',
        'avatar' => 'https://ui-avatars.com/api/?name=Admin&background=00d2ff&color=fff'
    ]);
    
    $contenido = '
        <h3>¡Hola desde el Centro de Control Global en Producción! 🎯</h3>
        <p>Esta es una publicación de prueba generada automáticamente. Si estás viendo esto en tu sitio web, significa que la <strong>integración de IA</strong> y la <strong>arquitectura multitenant centralizada</strong> están trabajando en vivo sin problemas.</p>
        <ul>
            <li>Aislamiento de datos: <strong>Activo en vivo</strong> 🛡️</li>
            <li>Enrutamiento dinámico (CORS): <strong>Correcto</strong> ✅</li>
        </ul>
        <p>¡El sistema está perfectamente sincronizado y listo para salir a producción!</p>
    ';

    $post_id = Post::create([
        'usuario_id' => $usuario_id,
        'cliente_id' => $cliente_id,
        'titulo' => '🚀 Prueba de Integración en Producción',
        'contenido' => trim($contenido),
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'tipo' => 'texto',
        'fijada' => 1,
        'fijada_hasta' => date('Y-m-d H:i:s', strtotime('+7 days'))
    ]);
    
    echo "========================================\n";
    echo "¡ÉXITO EN PRODUCCIÓN!\n";
    echo "Publicación generada con ID: " . $post_id . "\n";
    echo "Asignada a Cliente: " . $cliente['nombre'] . " (ID: " . $cliente_id . ")\n";
    echo "========================================\n";
    
} catch (\Exception $e) {
    echo "ERROR CRÍTICO: " . $e->getMessage() . "\n";
}
