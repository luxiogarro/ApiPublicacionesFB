<?php
$page = 'download_spec';
$pageTitle = 'Hub de Descargas';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/includes/header.php';

use App\Core\Database;

$apiKey  = 'VER_CLIENTES_API_KEYS';
$baseUrl = rtrim(BASE_URL, '/') . '/';

try {
    $db = Database::getInstance()->getConnection();
    $r  = $db->query("SELECT api_key FROM clientes LIMIT 1")->fetch();
    if ($r) $apiKey = $r['api_key'];
} catch (Exception $e) { /* usa el placeholder */}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>document.addEventListener('DOMContentLoaded',()=>hljs.highlightAll());</script>
<style>
.hub-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:1.5rem;margin-bottom:2rem}
.dl-card{background:white;border-radius:16px;border:1px solid var(--border);box-shadow:var(--shadow);padding:1.5rem;display:flex;flex-direction:column;gap:1rem}
.dl-card .icon{font-size:2.5rem}
.dl-card h3{font-size:1.05rem;font-weight:700;margin:0}
.dl-card p{font-size:.85rem;color:var(--text-muted);margin:0;flex:1}
.btn-dl{display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.2rem;border-radius:10px;text-decoration:none;font-weight:700;font-size:.88rem;cursor:pointer;border:none;transition:opacity .2s}
.btn-dl:hover{opacity:.85}
.preview-box{background:#1e1e1e;border-radius:12px;overflow:hidden;margin-bottom:2rem}
.preview-box-header{background:#2d2d2d;padding:.75rem 1.25rem;display:flex;justify-content:space-between;align-items:center;font-size:.82rem;color:#d4d4d4}
.copy-btn{background:none;border:1px solid #555;color:#aaa;padding:4px 12px;border-radius:6px;cursor:pointer;font-size:.78rem;transition:all .2s}
.copy-btn:hover,.copy-btn.copied{background:#4ade80;color:#000;border-color:#4ade80}
</style>

<div class="admin-header">
    <h1><i class="fas fa-download" style="color:var(--primary);"></i> Hub de Descargas</h1>
    <p style="color:var(--text-muted);margin-top:.5rem">Código del feed listo para integrar en tu sitio web.</p>
</div>

<div class="hub-grid">
    <div class="dl-card">
        <span class="icon">📜</span>
        <h3>feed.js — JavaScript</h3>
        <p>Lógica completa del feed. Tu API Key y URL ya incluidas.</p>
        <button class="btn-dl" style="background:#3b82f6;color:white" onclick="copiar('js-code')">
            <i class="fas fa-copy"></i> Copiar JS
        </button>
    </div>
    <div class="dl-card">
        <span class="icon">🌐</span>
        <h3>feed.html — Página Completa</h3>
        <p>HTML listo para subir a tu servidor con CSS y JS incluidos.</p>
        <button class="btn-dl" style="background:#10b981;color:white" onclick="copiar('html-code')">
            <i class="fas fa-copy"></i> Copiar HTML
        </button>
    </div>
    <div class="dl-card">
        <span class="icon">🔑</span>
        <h3>Credenciales Activas</h3>
        <p>URL y API Key de tu primer cliente para usar en cualquier integración.</p>
        <div style="font-family:monospace;background:#f0f7ff;border-radius:8px;padding:.75rem;font-size:.8rem;word-break:break-all">
            <strong>URL base:</strong><br><?php echo htmlspecialchars($baseUrl); ?><br><br>
            <strong>API Key:</strong><br><?php echo htmlspecialchars($apiKey); ?>
        </div>
    </div>
</div>

<?php
// NOWDOC: PHP no interpreta los ${...} de JS
$jsCode = str_replace(
    ['__BASE__', '__KEY__'],
    [$baseUrl,   $apiKey],
    <<<'JS'
const API_URL    = '__BASE__publicaciones';
const API_KEY    = '__KEY__';
const UPLOADS    = '__BASE__uploads/';

async function cargarFeed(id = 'feed') {
    const feed = document.getElementById(id);
    if (!feed) return;
    feed.innerHTML = '<p style="text-align:center;color:#6b7280;padding:2rem">Cargando...</p>';
    try {
        const res   = await fetch(API_URL, { headers: { 'X-API-KEY': API_KEY } });
        const posts = await res.json();
        if (!Array.isArray(posts) || !posts.length) {
            feed.innerHTML = '<p style="text-align:center;color:#999">Sin publicaciones.</p>';
            return;
        }
        feed.innerHTML = '';
        posts.forEach(post => {
            const imgExts = ['jpg','jpeg','png','gif','webp'];
            let portada = null;
            const imgs = [], docs = [];
            (post.adjuntos || []).forEach(a => {
                const ext = a.nombre_original.split('.').pop().toLowerCase();
                if (imgExts.includes(ext)) { imgs.push(a); if (a.es_portada == 1) portada = {...a, tipo:'imagen'}; }
                else { docs.push(a); if (a.es_portada == 1) portada = {...a, tipo:'doc'}; }
            });
            if (!portada && imgs.length) portada = {...imgs[0], tipo:'imagen'};
            const mins = imgs.filter(i => !portada || i.id !== portada.id);
            const fijado = post.es_fijada_activa == 1;
            const card = document.createElement('div');
            card.className = 'post-card' + (fijado ? ' is-pinned' : '');
            card.innerHTML = `
                ${fijado ? '<div class="pinned-badge"><i class="fas fa-thumbtack"></i> Publicación Fijada</div>' : ''}
                <div class="post-header">
                    ${post.usuario_avatar ? `<img src="${post.usuario_avatar}" class="avatar-img">` : `<div class="avatar-placeholder">${post.usuario_nombre.charAt(0)}</div>`}
                    <div><h4 class="author-name">${post.usuario_nombre}</h4>
                    <div class="post-meta">${new Date(post.created_at).toLocaleDateString('es-PE')}</div></div>
                </div>
                <div class="post-content-area">
                    ${post.titulo ? `<h3 class="post-title">${post.titulo}</h3>` : ''}
                    <div class="post-content">${post.contenido}</div>
                </div>
                ${post.video_url && post.video_url.includes('youtube') ? `<div class="video-container"><iframe src="https://www.youtube.com/embed/${post.video_url.split('v=')[1]}" frameborder="0" allowfullscreen></iframe></div>` : ''}
                ${portada && portada.tipo === 'imagen' ? `<div class="social-grid"><img src="${UPLOADS + portada.ruta_archivo}" class="featured-image" alt=""></div>` : ''}
            `;
            feed.appendChild(card);
        });
    } catch(e) {
        feed.innerHTML = `<p style="color:red">Error: ${e.message}</p>`;
    }
}
cargarFeed();
JS
);

$htmlCode = str_replace(
    ['__BASE__', '__KEY__'],
    [$baseUrl,   $apiKey],
    <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed de Publicaciones</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body{font-family:sans-serif;background:#f4f6f8;margin:0;padding:2rem 1rem}
        #feed{max-width:680px;margin:0 auto}
        .post-card{background:#fff;border-radius:16px;border:1px solid #e5e7eb;box-shadow:0 4px 12px rgba(0,0,0,.06);margin-bottom:2rem;overflow:hidden}
        .post-card.is-pinned{border:3px solid #ffd32a}
        .pinned-badge{background:linear-gradient(135deg,#ffd32a,#f9a825);color:#5c3900;padding:.55rem 1.25rem;font-weight:800;font-size:.82rem}
        .post-header{padding:1.25rem;display:flex;align-items:center;gap:.75rem}
        .avatar-img{width:45px;height:45px;border-radius:50%;object-fit:cover}
        .avatar-placeholder{width:45px;height:45px;border-radius:50%;background:linear-gradient(135deg,#0062ff,#60a5fa);color:#fff;font-weight:700;font-size:1.2rem;display:flex;align-items:center;justify-content:center}
        .author-name{font-weight:700;margin:0}
        .post-content-area{padding:0 1.25rem 1.25rem}
        .post-title{font-weight:800;font-size:1.15rem;margin-bottom:.5rem}
        .post-content{font-size:1rem;line-height:1.7;color:#374151}
        .featured-image{width:100%;max-height:500px;object-fit:contain;background:#f4f6f8;display:block}
        .video-container{position:relative;padding-bottom:56.25%;height:0;overflow:hidden;margin:0 1.25rem 1.25rem;border-radius:12px;background:#000}
        .video-container iframe{position:absolute;top:0;left:0;width:100%;height:100%}
    </style>
</head>
<body>
    <div id="feed"></div>
    <script>
    /* == PEGA AQUÍ EL CONTENIDO COMPLETO DE feed.js == */
    const API_URL = '__BASE__publicaciones';
    const API_KEY = '__KEY__';
    const UPLOADS = '__BASE__uploads/';
    /* ================================================= */
    cargarFeed();
    </script>
</body>
</html>
HTML
);
?>

<div class="preview-box">
    <div class="preview-box-header">
        <span>📄 feed.js — JavaScript del feed</span>
        <button class="copy-btn" onclick="copiar('js-code')"><i class="fas fa-copy"></i> Copiar todo</button>
    </div>
    <pre style="margin:0;max-height:520px;overflow:auto;padding:1.25rem"><code class="language-javascript" id="js-code"><?php echo htmlspecialchars($jsCode); ?></code></pre>
</div>

<div class="preview-box">
    <div class="preview-box-header">
        <span>🌐 feed.html — Página completa lista para usar</span>
        <button class="copy-btn" onclick="copiar('html-code')"><i class="fas fa-copy"></i> Copiar todo</button>
    </div>
    <pre style="margin:0;max-height:420px;overflow:auto;padding:1.25rem"><code class="language-html" id="html-code"><?php echo htmlspecialchars($htmlCode); ?></code></pre>
</div>

<script>
function copiar(id) {
    const texto = document.getElementById(id).innerText;
    navigator.clipboard.writeText(texto).then(() => {
        document.querySelectorAll('.copy-btn, .btn-dl').forEach(b => {
            if (b.getAttribute('onclick') && b.getAttribute('onclick').includes(id)) {
                const orig = b.innerHTML;
                b.classList.add('copied');
                b.innerHTML = '<i class="fas fa-check"></i> ¡Copiado!';
                setTimeout(() => { b.classList.remove('copied'); b.innerHTML = orig; }, 2000);
            }
        });
    }).catch(() => alert('Usa Ctrl+A para seleccionar y Ctrl+C para copiar.'));
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
