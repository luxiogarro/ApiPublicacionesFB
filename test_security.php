<?php
/**
 * Test de Seguridad y Aislamiento (Multitenancy)
 */

$baseUrl = "http://localhost/ApiPublicacionesFB/";
$keyA = "test_key_123"; // Cliente A
$keyB = "key_cliente_2"; // Cliente B (asumiendo que existe o lo crearemos)

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

echo "--- Iniciando Pruebas de Seguridad ---\n";

// 1. Crear post con Cliente A
echo "[1] Cliente A crea publicación... ";
$res = request('publicaciones', 'POST', $keyA, ['usuario_nombre' => 'Admin A', 'contenido' => 'Post de Cliente A']);
$postId = $res['body']['post_id'] ?? null;
echo ($postId ? "OK (ID: $postId)" : "FAIL") . "\n";

// 2. Cliente B intenta ver posts (no debería ver el de A)
echo "[2] Cliente B consulta feed... ";
$res = request('publicaciones', 'GET', $keyB);
$found = false;
if (isset($res['body']) && is_array($res['body'])) {
    if (isset($res['body']['error'])) {
        echo "INFO (Respuesta de error de API: {$res['body']['error']})\n";
    } else {
        foreach ($res['body'] as $p) {
            if (is_array($p) && isset($p['id']) && $p['id'] == $postId) $found = true;
        }
        echo ($found ? "FAIL (Vio post ajeno)" : "OK (Aislamiento verificado)") . "\n";
    }
} else {
    echo "FAIL (Respuesta inválida: " . json_encode($res['body']) . ")\n";
}

// 3. Cliente B intenta borrar post de A
echo "[3] Cliente B intenta borrar post de A... ";
$res = request("publicaciones/$postId", 'DELETE', $keyB);
echo ($res['code'] == 404 ? "OK (Bloqueado 404)" : "FAIL (Código: {$res['code']})") . "\n";

// 4. Cliente A borra su propio post (Ruta dinámica)
echo "[4] Cliente A borra su propio post... ";
$res = request("publicaciones/$postId", 'DELETE', $keyA);
echo ($res['code'] == 200 ? "OK" : "FAIL (Código: {$res['code']})") . "\n";

// 5. Cliente A intenta borrar post borrado
echo "[5] Cliente A intenta borrar post inexistente... ";
$res = request("publicaciones/$postId", 'DELETE', $keyA);
echo ($res['code'] == 404 ? "OK" : "FAIL (Código: {$res['code']})") . "\n";
