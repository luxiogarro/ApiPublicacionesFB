<?php
// admin/test_unitario.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/autoload.php';

use App\Core\Database;
use App\Model\Post;
use App\Model\User;

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><title>Unit Tests - API Publicaciones</title>";
echo "<style>
    body { font-family: 'Courier New', monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
    .success { color: #4CAF50; }
    .error { color: #f44336; font-weight: bold; }
    .info { color: #569cd6; }
    .warning { color: #ffeb3b; }
    .card { background: #2d2d2d; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #569cd6; }
</style></head><body>";

echo "<h2>🧪 Ejecución de Pruebas Unitarias (Producción)</h2>";

try {
    echo "<div class='card'>";
    // 1. DATABASE CONNECTION
    echo "<h3>1. Conexión a Base de Datos</h3>";
    $db = Database::getInstance()->getConnection();
    echo "<span class='success'>[SUCCESS]</span> Conectado a la BD exitosamente.<br>";

    // 2. CORE: CLIENT CREATION & CHECK
    echo "<h3>2. Modelo: Cliente (Requisito)</h3>";
    $stmt = $db->query("SELECT id FROM clientes LIMIT 1");
    $cliente = $stmt->fetch();
    if (!$cliente) {
        echo "<span class='warning'>[INFO]</span> No hay clientes en la BD. Creando un 'Cliente de Prueba'...<br>";
        $db->query("INSERT INTO clientes (nombre, api_key) VALUES ('Cliente de Prueba', 'test_key_" . time() . "')");
        $cliente_id = $db->lastInsertId();
        echo "<span class='success'>[SUCCESS]</span> Cliente creado con ID: $cliente_id<br>";
    } else {
        $cliente_id = $cliente['id'];
        echo "<span class='success'>[SUCCESS]</span> Cliente existente encontrado con ID: $cliente_id<br>";
    }

    // 3. MODEL: USER (getOrCreate)
    echo "<h3>3. Modelo: User::getOrCreate()</h3>";
    $external_id = 'user_test_' . rand(1000, 9999);
    $usuario_id = User::getOrCreate($cliente_id, [
        'nombre' => 'Usuario Test API',
        'external_id' => $external_id
    ]);
    if ($usuario_id) {
        echo "<span class='success'>[SUCCESS]</span> Usuario obtenido/creado con ID: $usuario_id<br>";
    } else {
        throw new Exception("Fallo al crear o obtener el Usuario.");
    }

    // 4. MODEL: POST (create)
    echo "<h3>4. Modelo: Post::create() (Función de Publicar)</h3>";
    $post_data = [
        'usuario_id' => $usuario_id,
        'cliente_id' => $cliente_id,
        'titulo' => 'Publicación de Prueba Unitaria',
        'contenido' => '<p>Este es un post generado automáticamente para verificar el funcionamiento de la base de datos.</p>',
        'tipo' => 'texto',
        'video_url' => null,
        'fijada' => 1
    ];
    
    $post_id = Post::create($post_data);
    if ($post_id) {
        echo "<span class='success'>[SUCCESS]</span> Publicación creada exitosamente en la Base de Datos con ID: $post_id<br>";
    } else {
        throw new Exception("Post::create() devolvió un ID inválido o vacío.");
    }

    // 5. MODEL: POST (addAttachment)
    echo "<h3>5. Modelo: Post::addAttachment()</h3>";
    Post::addAttachment($post_id, [
        'ruta' => 'test_image.jpg',
        'tipo' => 'image/jpeg',
        'nombre' => 'test_image.jpg'
    ], 1);
    echo "<span class='success'>[SUCCESS]</span> Adjunto virtual vinculado al Post #$post_id correctamente.<br>";

    // 6. MODEL: POST (getAllGlobal)
    echo "<h3>6. Modelo: Post::getAllGlobal() (Lectura)</h3>";
    $posts = Post::getAllGlobal(5);
    if (count($posts) > 0) {
        echo "<span class='success'>[SUCCESS]</span> Lectura de publicaciones exitosa. Posts encontrados: " . count($posts) . "<br>";
    } else {
        throw new Exception("No se encontraron publicaciones después de haber creado una.");
    }

    // 7. CLEANUP
    echo "<h3>7. Limpieza de Datos (Rollback)</h3>";
    $db->query("DELETE FROM publicaciones WHERE id = $post_id");
    // El adjunto se borra solo por el ON DELETE CASCADE
    echo "<span class='success'>[SUCCESS]</span> Publicación de prueba eliminada para no ensuciar tu sistema.<br>";
    echo "</div>";

    echo "<h2 class='success'>✅ TODAS LAS PRUEBAS SE COMPLETARON CON EXITO. EL FLUJO ESTA AL 100%.</h2>";

} catch (PDOException $e) {
    echo "</div>";
    echo "<div class='card' style='border-left-color: #f44336;'>";
    echo "<h2 class='error'>❌ ERROR EN BASE DE DATOS (PDO)</h2>";
    echo "<span class='error'>Mensaje exacto:</span> " . htmlspecialchars($e->getMessage()) . "<br><br>";
    echo "<strong>Solución probable:</strong> Revisa que todas las columnas de tu tabla en phpMyAdmin coincidan con las locales.";
    echo "</div>";
} catch (Exception $e) {
    echo "</div>";
    echo "<div class='card' style='border-left-color: #f44336;'>";
    echo "<h2 class='error'>❌ ERROR EN LA LÓGICA PHP</h2>";
    echo "<span class='error'>Detalle:</span> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "</body></html>";
