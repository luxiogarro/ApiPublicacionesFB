<?php
$page = 'download_spec';
$pageTitle = 'Hub de Descargas - Código del Feed';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/includes/header.php';

use App\Core\Database;

// Obtener primer cliente para rellenar el ejemplo
$db = Database::getInstance()->getConnection();
$cliente = $db->query("SELECT api_key FROM clientes LIMIT 1")->fetch();
$apiKey = $cliente ? $cliente['api_key'] : 'TU_API_KEY_AQUI';
$baseUrl = BASE_URL;
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>document.addEventListener('DOMContentLoaded', () => hljs.highlightAll());</script>

<style>
    .hub-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .download-card {
        background: white; border-radius: 16px; border: 1px solid var(--border);
        box-shadow: var(--shadow); padding: 1.5rem;
        display: flex; flex-direction: column; gap: 1rem;
    }
    .download-card .icon { font-size: 2.5rem; }
    .download-card h3 { font-size: 1.05rem; font-weight: 700; margin: 0; }
    .download-card p { font-size: 0.85rem; color: var(--text-muted); margin: 0; flex: 1; }
    .btn-dl {
        display: inline-flex; align-items: center; gap: 0.5rem;
        padding: 0.65rem 1.2rem; border-radius: 10px;
        text-decoration: none; font-weight: 700; font-size: 0.88rem;
        transition: opacity .2s; width: fit-content;
    }
    .btn-dl:hover { opacity: 0.85; }
    .preview-box { background: #1e1e1e; border-radius: 12px; overflow: hidden; margin-bottom: 2rem; }
    .preview-box-header {
        background: #2d2d2d; padding: 0.75rem 1.25rem;
        display: flex; justify-content: space-between; align-items: center;
        font-size: 0.82rem; color: #d4d4d4;
    }
    .copy-btn {
        background: none; border: 1px solid #555; color: #aaa;
        padding: 4px 12px; border-radius: 6px; cursor: pointer; font-size: 0.78rem;
        transition: all .2s;
    }
    .copy-btn:hover { background: #4ade80; color: #000; border-color: #4ade80; }
    .copy-btn.copied { background: #4ade80; color: #000; border-color: #4ade80; }
    .section-title { font-size: 1.2rem; font-weight: 800; margin: 2rem 0 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #f0f2f5; }
</style>

<div class="admin-header">
    <h1><i class="fas fa-download" style="color:var(--primary);"></i> Hub de Descargas</h1>
    <p style="color: var(--text-muted); margin-top: 0.5rem;">
        Recursos listos para integrar en tu sitio web. Tu API Key ya está incluida.
    </p>
</div>

<?php
// Generar código del feed con los datos reales del servidor
$jsCode = <<<CODE
const API_URL = '{$baseUrl}publicaciones';
const API_KEY = '{$apiKey}';
const UPLOADS_URL = '{$baseUrl}uploads/';

async function cargarFeed(containerId = 'feed') {
    const feed = document.getElementById(containerId);
    if (!feed) return;
    feed.innerHTML = '<p style="text-align:center; color:#6b7280; padding:2rem;">Cargando publicaciones...</p>';
    try {
        const res   = await fetch(API_URL, { headers: { 'X-API-KEY': API_KEY } });
        const posts = await res.json();
        if (!posts.length) { feed.innerHTML = '<p style="text-align:center;color:#999;">Sin publicaciones aún.</p>'; return; }
        feed.innerHTML = '';
        posts.forEach(post => {
            const imgExts = ['jpg','jpeg','png','gif','webp'];
            const imagenes = [], docs = [];
            let portada = null;
            (post.adjuntos || []).forEach(a => {
                const ext = a.nombre_original.split('.').pop().toLowerCase();
                if (imgExts.includes(ext)) {
                    imagenes.push(a);
                    if (a.es_portada == 1) portada = { ...a, tipo: 'imagen' };
                } else {
                    docs.push(a);
                    if (a.es_portada == 1) portada = { ...a, tipo: 'documento' };
                }
            });
            if (!portada && imagenes.length) portada = { ...imagenes[0], tipo: 'imagen' };
            const miniaturas = imagenes.filter(i => !portada || i.id !== portada.id);
            const esFijado = post.es_fijada_activa == 1;
            const card = document.createElement('div');
            card.className = 'post-card' + (esFijado ? ' is-pinned' : '');
            card.innerHTML = `
                \${esFijado ? `<div class="pinned-badge"><i class="fas fa-thumbtack"></i> Publicación Fijada</div>` : ''}
                <div class="post-header">
                    \${post.usuario_avatar ? `<img src="\${post.usuario_avatar}" class="avatar-img" alt="\${post.usuario_nombre}">` : `<div class="avatar-placeholder">\${post.usuario_nombre.charAt(0)}</div>`}
                    <div>
                        <h4 class="author-name">\${post.usuario_nombre}</h4>
                        <div class="post-meta"><i class="far fa-clock"></i> \${new Date(post.created_at).toLocaleDateString('es-PE')}</div>
                    </div>
                </div>
                <div class="post-content-area">
                    \${post.titulo ? `<h3 class="post-title">\${post.titulo}</h3>` : ''}
                    <div class="post-content">\${post.contenido}</div>
                </div>
                \${renderPortada(portada, miniaturas)}
                \${renderVideo(post.video_url)}
            `;
            feed.appendChild(card);
        });
    } catch(e) {
        feed.innerHTML = `<p style="color:red;">Error al cargar el feed: \${e.message}</p>`;
    }
}

function renderVideo(url) {
    if (!url) return '';
    if (url.includes('<iframe')) return `<div class="video-container">\${url}</div>`;
    if (url.includes('youtube.com') || url.includes('youtu.be')) {
        const id = url.includes('v=') ? url.split('v=')[1].split('&')[0] : url.split('/').pop();
        return `<div class="video-container"><iframe src="https://www.youtube.com/embed/\${id}" frameborder="0" allowfullscreen></iframe></div>`;
    }
    return '';
}

function renderPortada(portada, miniaturas) {
    if (!portada) return '';
    const url = UPLOADS_URL + portada.ruta_archivo;
    if (portada.tipo === 'imagen') {
        let html = `<div class="social-grid"><img src="\${url}" class="featured-image" alt="Portada">`;
        if (miniaturas.length) {
            html += '<div class="thumbnails-row">';
            miniaturas.slice(0, 4).forEach((m, i) => {
                html += `<div class="thumbnail-wrapper"><img src="\${UPLOADS_URL + m.ruta_archivo}" class="thumbnail-item">
                    \${i === 3 && miniaturas.length > 4 ? `<div class="more-images-overlay">+\${miniaturas.length - 3}</div>` : ''}</div>`;
            });
            html += '</div>';
        }
        return html + '</div>';
    }
    return '';
}

cargarFeed();
CODE;

$htmlCode = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed de Publicaciones</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f6f8; margin: 0; padding: 2rem 1rem; }
        #feed { max-width: 680px; margin: 0 auto; }
        .post-card { background: #fff; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,0.06); margin-bottom: 2rem; overflow: hidden; transition: transform .2s; }
        .post-card:hover { transform: translateY(-2px); }
        .post-card.is-pinned { border: 3px solid #ffd32a; background: linear-gradient(to bottom, #fffde7, white); }
        .pinned-badge { background: linear-gradient(135deg,#ffd32a,#f9a825); color: #5c3900; padding: .55rem 1.25rem; font-weight: 800; font-size: .82rem; }
        .post-header { padding: 1.25rem; display: flex; align-items: center; gap: .75rem; }
        .avatar-img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; }
        .avatar-placeholder { width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg,#0062ff,#60a5fa); color: white; font-weight: 700; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; }
        .author-name { font-weight: 700; margin: 0; }
        .post-meta { font-size: .8rem; color: #6b7280; }
        .post-content-area { padding: 0 1.25rem 1.25rem; }
        .post-title { font-weight: 800; font-size: 1.15rem; margin-bottom: .5rem; }
        .post-content { font-size: 1rem; line-height: 1.7; color: #374151; }
        .social-grid { display: flex; flex-direction: column; }
        .featured-image { width: 100%; max-height: 500px; object-fit: contain; background: #f4f6f8; display: block; }
        .thumbnails-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; padding: 4px 0 0; }
        .thumbnail-wrapper { position: relative; aspect-ratio: 1/1; overflow: hidden; border-radius: 6px; }
        .thumbnail-item { width: 100%; height: 100%; object-fit: cover; }
        .more-images-overlay { position: absolute; inset: 0; background: rgba(0,0,0,.6); color: white; font-weight: 800; font-size: 1.8rem; display: flex; align-items: center; justify-content: center; }
        .video-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; margin: 0 1.25rem 1.25rem; border-radius: 12px; background: #000; }
        .video-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
    </style>
</head>
<body>
    <div id="feed"></div>
    <script>
        /* === CÓDIGO DEL FEED — GENERADO AUTOMÁTICAMENTE === */
        const API_URL   = '{$baseUrl}publicaciones';
        const API_KEY   = '{$apiKey}';
        const UPLOADS_URL = '{$baseUrl}uploads/';
        /* Pega aquí el resto del JS de la sección anterior */
        cargarFeed();
    </script>
</body>
</html>
HTML;
?>

<!-- TARJETAS DE DESCARGA DIRECTA -->
<h2 class="section-title"><i class="fas fa-box-open"></i> Descargas Directas</h2>
<div class="hub-grid">
    <!-- JS del Feed -->
    <div class="download-card">
        <span class="icon">📜</span>
        <h3>feed.js — JavaScript del Feed</h3>
        <p>El código JavaScript completo con tu API Key y URL ya configuradas. Incluye renderizado de posts, imágenes, PDFs y videos.</p>
        <a href="generate_download.php?tipo=js" class="btn-dl" style="background:#3b82f6; color:white;">
            <i class="fas fa-download"></i> Descargar feed.js
        </a>
    </div>

    <!-- HTML Completo -->
    <div class="download-card">
        <span class="icon">🌐</span>
        <h3>feed.html — Página Completa</h3>
        <p>Una página HTML lista para subir a tu servidor con todo incluido: CSS, JS y la estructura del feed.</p>
        <a href="generate_download.php?tipo=html" class="btn-dl" style="background:#10b981; color:white;">
            <i class="fas fa-download"></i> Descargar feed.html
        </a>
    </div>

    <!-- CSS -->
    <div class="download-card">
        <span class="icon">🎨</span>
        <h3>feed.css — Estilos del Feed</h3>
        <p>La hoja de estilos completa. Inclúyela en tu web para replicar el aspecto visual del feed.</p>
        <a href="generate_download.php?tipo=css" class="btn-dl" style="background:#8b5cf6; color:white;">
            <i class="fas fa-download"></i> Descargar feed.css
        </a>
    </div>
</div>

<!-- PREVIEW DEL CÓDIGO JS -->
<h2 class="section-title"><i class="fas fa-code"></i> Vista Previa del Código</h2>

<div class="preview-box">
    <div class="preview-box-header">
        <span>📄 feed.js — Tu API Key ya está incluida</span>
        <button class="copy-btn" onclick="copiarCodigo(this, 'js-code')">
            <i class="fas fa-copy"></i> Copiar
        </button>
    </div>
    <pre style="margin:0; max-height:500px; overflow:auto;"><code class="language-javascript" id="js-code"><?php echo htmlspecialchars($jsCode); ?></code></pre>
</div>

<div class="preview-box">
    <div class="preview-box-header">
        <span>🌐 feed.html — Ejemplo completo lista para usar</span>
        <button class="copy-btn" onclick="copiarCodigo(this, 'html-code')">
            <i class="fas fa-copy"></i> Copiar
        </button>
    </div>
    <pre style="margin:0; max-height:400px; overflow:auto;"><code class="language-html" id="html-code"><?php echo htmlspecialchars($htmlCode); ?></code></pre>
</div>

<script>
function copiarCodigo(btn, id) {
    const texto = document.getElementById(id).innerText;
    navigator.clipboard.writeText(texto).then(() => {
        btn.classList.add('copied');
        btn.innerHTML = '<i class="fas fa-check"></i> ¡Copiado!';
        setTimeout(() => {
            btn.classList.remove('copied');
            btn.innerHTML = '<i class="fas fa-copy"></i> Copiar';
        }, 2500);
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
