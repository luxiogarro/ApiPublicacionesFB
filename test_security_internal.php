<?php
require_once 'config/config.php';
require_once 'src/Core/Database.php';

$db = \App\Core\Database::getInstance()->getConnection();

// Asegurar existencia de 2 clientes para la prueba
$db->exec("INSERT IGNORE INTO clientes (id, nombre, api_key) VALUES (1, 'Cliente A', 'test_key_123')");
$db->exec("INSERT IGNORE INTO clientes (id, nombre, api_key) VALUES (2, 'Cliente B', 'key_cliente_2')");

$keyA = "test_key_123";
$keyB = "key_cliente_2";
$baseUrl = "http://localhost/ApiPublicacionesFB/";

function request($path, $method, $key, $data = []) {
    global $baseUrl;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-API-KEY: $key", "Content-Type: application/json"]);
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return ['code' => $info['http_code'], 'body' => json_decode($res, true)];
}

echo "--- Iniciando Pruebas de Seguridad (Interna) ---\n";

// 1. Crear post con Cliente A
echo "[1] Cliente A crea publicación... ";
$res = request('publicaciones', 'POST', $keyA, ['usuario_nombre' => 'Admin A', 'contenido' => 'Aislamiento Test']);
$postId = $res['body']['post_id'] ?? null;
echo ($postId ? "OK (ID: $postId)" : "FAIL " . json_encode($res)) . "\n";

// 2. Cliente B intenta ver posts (no debería ver el de A)
echo "[2] Cliente B consulta feed... ";
$res = request('publicaciones', 'GET', $keyB);
$found = false;
if (isset($res['body']) && is_array($res['body'])) {
    foreach ($res['body'] as $p) {
        if (isset($p['id']) && $p['id'] == $postId) $found = true;
    }
}
echo ($found ? "FAIL (Aislamiento falló)" : "OK (Aislamiento verificado)") . "\n";

// 3. Cliente B intenta modificar post de A
echo "[3] Cliente B intenta modificar post de A... ";
$res = request("publicaciones/$postId", 'PUT', $keyB, ['contenido' => 'Hackeado']);
echo ($res['code'] == 500 || $res['code'] == 404 ? "OK (Bloqueado)" : "FAIL (Código: {$res['code']})") . "\n";

// 4. Cliente B intenta borrar post de A
echo "[4] Cliente B intenta borrar post de A... ";
$res = request("publicaciones/$postId", 'DELETE', $keyB);
echo ($res['code'] == 404 ? "OK (Bloqueado)" : "FAIL (Código: {$res['code']})") . "\n";

// 5. Cliente A borra su post
echo "[5] Cliente A borra su propio post... ";
$res = request("publicaciones/$postId", 'DELETE', $keyA);
echo ($res['code'] == 200 ? "OK" : "FAIL (Código: {$res['code']})") . "\n";
