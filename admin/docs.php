<?php
$page = 'docs';
$pageTitle = 'Documentación de la API';
require_once __DIR__ . '/includes/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>hljs.highlightAll();</script>

<style>
    .docs-layout { display: grid; grid-template-columns: 240px 1fr; gap: 2rem; align-items: start; }
    .docs-sidebar {
        position: sticky; top: 1rem;
        background: white; border-radius: var(--radius); border: 1px solid var(--border);
        padding: 1.5rem; box-shadow: var(--shadow);
    }
    .docs-sidebar h3 { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.75rem; }
    .docs-sidebar a { display: block; padding: 0.4rem 0.6rem; border-radius: 6px; color: var(--text-dark); text-decoration: none; font-size: 0.88rem; margin-bottom: 2px; transition: all 0.2s; }
    .docs-sidebar a:hover { background: #f0f7ff; color: var(--primary); }
    .docs-sidebar a.active { background: #e7f0ff; color: var(--primary); font-weight: 600; }

    .doc-section { background: white; border-radius: var(--radius); border: 1px solid var(--border); padding: 2rem; box-shadow: var(--shadow); margin-bottom: 2rem; scroll-margin-top: 1rem; }
    .doc-section h2 { font-size: 1.3rem; font-weight: 800; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 2px solid #f0f2f5; display: flex; align-items: center; gap: 0.6rem; }
    .doc-section h3 { font-size: 1rem; font-weight: 700; margin: 1.5rem 0 0.75rem; color: var(--text-dark); }

    .method-badge { display: inline-block; padding: 3px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.5px; }
    .method-get    { background: #d1fae5; color: #065f46; }
    .method-post   { background: #dbeafe; color: #1e40af; }
    .method-delete { background: #fee2e2; color: #991b1b; }

    .endpoint-url { font-family: monospace; background: #f8f9fa; padding: 0.6rem 1rem; border-radius: 8px; border: 1px solid #e5e7eb; color: #111; font-size: 0.95rem; margin: 0.5rem 0 1rem; }

    pre { margin: 0; }
    pre code { border-radius: 10px !important; font-size: 0.82rem !important; }

    .param-table { overflow-x: auto; margin: 0.5rem 0; }
    .param-table table { width: 100%; border-collapse: collapse; }
    .param-table th { background: #f8f9fa; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); padding: 0.5rem 1rem; text-align: left; }
    .param-table td { padding: 0.6rem 1rem; border-bottom: 1px solid #f0f2f5; font-size: 0.88rem; vertical-align: top; }
    .param-table tr:last-child td { border-bottom: none; }
    .param-table code { background: #f0f2f5; padding: 2px 6px; border-radius: 4px; font-size: 0.82rem; color: #d63384; }
    .badge-required { background: #fef3c7; color: #92400e; font-size: 0.68rem; font-weight: 700; padding: 2px 7px; border-radius: 10px; white-space: nowrap; }
    .badge-optional { background: #e5e7eb; color: #6b7280; font-size: 0.68rem; font-weight: 700; padding: 2px 7px; border-radius: 10px; white-space: nowrap; }

    .alert-info    { background: #eff6ff; border-left: 4px solid var(--primary); padding: 0.9rem 1rem; border-radius: 0 8px 8px 0; margin: 1rem 0; font-size: 0.88rem; }
    .alert-warn    { background: #fffbeb; border-left: 4px solid #f59e0b; padding: 0.9rem 1rem; border-radius: 0 8px 8px 0; margin: 1rem 0; font-size: 0.88rem; }
    .alert-success { background: #f0fdf4; border-left: 4px solid #22c55e; padding: 0.9rem 1rem; border-radius: 0 8px 8px 0; margin: 1rem 0; font-size: 0.88rem; }
    .alert-purple  { background: #f5f3ff; border-left: 4px solid #8b5cf6; padding: 0.9rem 1rem; border-radius: 0 8px 8px 0; margin: 1rem 0; font-size: 0.88rem; }

    .tabs { display: flex; gap: 4px; margin-bottom: 0; }
    .tab-btn { padding: 6px 14px; border: none; background: #e5e7eb; border-radius: 8px 8px 0 0; cursor: pointer; font-size: 0.82rem; font-weight: 600; color: #6b7280; transition: 0.2s; }
    .tab-btn.active { background: #282c34; color: white; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }

    .change-tag { display: inline-block; background: #dcfce7; color: #15803d; font-size: 0.68rem; font-weight: 700; padding: 1px 7px; border-radius: 10px; margin-left: 6px; }
</style>

<div class="admin-header" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h1><i class="fas fa-book-open" style="color:var(--primary);"></i> Documentación de la API</h1>
        <p style="color: var(--text-muted); margin-top: 0.5rem;">Guía completa de integración — implementa el feed de publicaciones en cualquier sitio web.</p>
    </div>
    <a href="debug_docs.php" class="btn-download" style="background:#10b981; color:white; padding: 0.8rem 1.2rem; border-radius:12px; font-size:0.9rem; text-decoration:none; display:flex; align-items:center; gap:0.5rem;">
        <i class="fas fa-vial"></i> Abrir Hub de Descargas
    </a>
</div>

<div class="docs-layout">

    <!-- ── Sidebar ── -->
    <aside class="docs-sidebar">
        <h3>Índice</h3>
        <a href="#intro">Introducción</a>
        <a href="#auth">Autenticación</a>
        <a href="#get-posts">Obtener Publicaciones</a>
        <a href="#create-post">Crear Publicación</a>
        <a href="#delete-post">Eliminar Publicación</a>
        <a href="#response-format">Errores HTTP</a>
        <a href="#usuarios">Usuarios y Avatar</a>
        <a href="#pinned">Publicaciones Fijadas</a>
        <a href="#images">Imágenes y Adjuntos</a>
        <a href="#documents">Documentos</a>
        <a href="#render-feed">Renderizar el Feed (JS)</a>
        <a href="#css-guide">Estilos CSS Completos</a>
        <a href="#examples">Ejemplo HTML Completo</a>
    </aside>

    <div>

        <!-- ── INTRO ── -->
        <div class="doc-section" id="intro">
            <h2><i class="fas fa-rocket" style="color:var(--primary);"></i> Introducción</h2>
            <p>Esta API REST centraliza la gestión de publicaciones para múltiples sitios web desde un único panel. Cada sitio web tiene una <strong>API Key</strong> propia que lo identifica.</p>
            <div class="alert-info">
                <strong><i class="fas fa-server"></i> URL Base:</strong><br>
                <code>http://TU_DOMINIO/ApiPublicacionesFB</code><br>
                <small>Reemplaza <code>TU_DOMINIO</code> con la IP o dominio donde está instalada la API.</small>
            </div>
            <div class="alert-warn">
                <strong><i class="fas fa-exclamation-triangle"></i> Requisitos del servidor:</strong> Apache con <code>mod_rewrite</code> activado. El archivo <code>.htaccess</code> debe estar en la raíz del proyecto.
            </div>
        </div>

        <!-- ── AUTH ── -->
        <div class="doc-section" id="auth">
            <h2><i class="fas fa-key" style="color:#f59e0b;"></i> Autenticación</h2>
            <p>Todas las peticiones deben incluir la cabecera HTTP <code>X-API-KEY</code> con la clave asignada a tu sitio web. La clave se obtiene en el panel → <a href="clientes.php">Clientes &amp; API Keys</a>.</p>
            <div class="endpoint-url"><i class="fas fa-lock" style="color:#aaa;margin-right:6px;"></i><strong>X-API-KEY: tu_clave_api</strong></div>
            <pre><code class="language-bash">curl -H "X-API-KEY: tu_clave_api" \
     http://TU_DOMINIO/ApiPublicacionesFB/publicaciones</code></pre>
        </div>

        <!-- ── GET POSTS ── -->
        <div class="doc-section" id="get-posts">
            <h2><i class="fas fa-download" style="color:#22c55e;"></i> Obtener Publicaciones</h2>
            <span class="method-badge method-get">GET</span>
            <div class="endpoint-url">/publicaciones</div>
            <p>No requiere parámetros. Devuelve todas las publicaciones del cliente ordenadas con los <strong>posts fijados activos primero</strong>, luego el resto cronológicamente.</p>

            <h3>Respuesta JSON:</h3>
<?php
$json_get_example = <<<'EOD'
[
  {
    "id": 20,
    "titulo": "Webinar Especial ISO 37001",
    "contenido": "<p>Contenido en <strong>HTML</strong>...</p>",
    "video_url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
    "tipo": "multiple",
    "created_at": "2026-02-21 23:14:00",
    "usuario_nombre": "Admin Global",
    "usuario_avatar": "https://tusitio.com/avatars/admin.jpg",
    "fijada": 1,
    "fijada_hasta": "2026-02-22 01:06:00",
    "es_fijada_activa": 1,
    "adjuntos": [
      {
        "id": 45,
        "ruta_archivo": "abc123.webp",
        "tipo_archivo": "image/webp",
        "nombre_original": "foto.jpg",
        "es_portada": 1
      }
    ]
  }
]
EOD;
?>
            <pre><code class="language-json"><?php echo htmlspecialchars($json_get_example); ?></code></pre>

            <h3>URL para acceder a los archivos:</h3>
            <div class="endpoint-url">http://TU_DOMINIO/ApiPublicacionesFB/uploads/<strong>{ruta_archivo}</strong></div>

            <div class="param-table">
                <table>
                    <thead><tr><th>Campo del adjunto</th><th>Tipo</th><th>Descripción</th></tr></thead>
                    <tbody>
                        <tr><td><code>ruta_archivo</code></td><td>String</td><td>Nombre del archivo en el servidor. Combina con la URL base de uploads.</td></tr>
                        <tr><td><code>tipo_archivo</code></td><td>String</td><td>MIME type: <code>image/webp</code>, <code>application/pdf</code>, etc.</td></tr>
                        <tr><td><code>nombre_original</code></td><td>String</td><td>Nombre original que subió el usuario.</td></tr>
                        <tr><td><code>es_portada</code></td><td>0 | 1</td><td>Si es <code>1</code>, es el archivo destacado principal de la publicación.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── CREATE POST ── -->
        <div class="doc-section" id="create-post">
            <h2><i class="fas fa-paper-plane" style="color:var(--primary);"></i> Crear Publicación</h2>
            <span class="method-badge method-post">POST</span>
            <div class="endpoint-url">/publicaciones</div>
            <p>Envía como <code>multipart/form-data</code> (obligatorio si adjuntas archivos).</p>

            <div class="param-table">
                <table>
                    <thead><tr><th>Parámetro</th><th>Tipo</th><th></th><th>Descripción</th></tr></thead>
                    <tbody>
                        <tr><td><code>usuario_nombre</code></td><td>String</td><td><span class="badge-required">Requerido</span></td><td>Nombre visible del autor en el feed.</td></tr>
                        <tr><td><code>contenido</code></td><td>String</td><td><span class="badge-required">Requerido</span></td><td>Texto de la publicación. Acepta HTML básico (párrafos, negritas, listas).</td></tr>
                        <tr><td><code>titulo</code></td><td>String</td><td><span class="badge-optional">Opcional</span></td><td>Título de la publicación (aparece en negrita encima del contenido).</td></tr>
                        <tr><td><code>usuario_email</code></td><td>String</td><td><span class="badge-optional">Opcional</span></td><td>Email del autor. Sirve para identificar al usuario en llamadas posteriores.</td></tr>
                        <tr><td><code>usuario_external_id</code></td><td>String</td><td><span class="badge-optional">Opcional</span></td><td>ID del usuario en tu sistema (p.ej. el ID de tu BD). Recomendado para una correcta identificación.</td></tr>
                         <tr><td><code>usuario_avatar</code></td><td>String (URL)</td><td><span class="badge-optional">Opcional</span></td><td>URL de la foto de perfil del autor. Se guarda al crear y <strong>se actualiza automáticamente</strong> en cada publicación posterior del mismo usuario.</td></tr>
                        <tr><td><code>video_url</code></td><td>String</td><td><span class="badge-optional">Opcional</span></td><td>URL de Video (YouTube, Vimeo, Facebook) o código <code>&lt;iframe&gt;</code> completo. El sistema detecta automáticamente si es una URL y la convierte en un reproductor.</td></tr>
                        <tr><td><code>tipo</code></td><td>String</td><td><span class="badge-optional">Opcional</span></td><td><code>texto</code>, <code>imagen</code>, <code>documento</code> o <code>multiple</code>. Default: <code>texto</code>.</td></tr>
                        <tr><td><code>archivos[]</code></td><td>File[]</td><td><span class="badge-optional">Opcional</span></td><td>Array de archivos adjuntos. Las imágenes se convierten automáticamente a WebP y se redimensionan a 1200px máx.</td></tr>
                    </tbody>
                </table>
            </div>

            <h3>Ejemplos:</h3>
            <div class="tabs">
                <button class="tab-btn active" onclick="showTab('php-post', this)">PHP (cURL)</button>
                <button class="tab-btn" onclick="showTab('js-post', this)">JavaScript (fetch)</button>
                <button class="tab-btn" onclick="showTab('bash-post', this)">curl (terminal)</button>
            </div>
            <div id="php-post" class="tab-content active">
<pre><code class="language-php">&lt;?php
$ch = curl_init('http://TU_DOMINIO/ApiPublicacionesFB/publicaciones');
$data = [
    'usuario_nombre'      => 'Juan Pérez',
    'usuario_email'       => 'juan@empresa.com',
    'usuario_external_id' => 'usr_001',
    'usuario_avatar'      => 'https://tusitio.com/avatars/juan.jpg',
    'titulo'              => 'Mi primera publicación',
    'contenido'           => '&lt;p&gt;Texto &lt;strong&gt;enriquecido&lt;/strong&gt; aquí.&lt;/p&gt;',
    'tipo'                => 'multiple',
    // Archivos opcionales:
    'archivos[0]'         => new CURLFile('/ruta/imagen.jpg', 'image/jpeg', 'foto.jpg'),
    'archivos[1]'         => new CURLFile('/ruta/informe.pdf', 'application/pdf', 'informe.pdf'),
];
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $data,
    CURLOPT_HTTPHEADER     => ['X-API-KEY: tu_clave_api'],
    CURLOPT_RETURNTRANSFER => true,
]);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);
echo "Post creado con ID: " . $response['post_id'];</code></pre>
            </div>
            <div id="js-post" class="tab-content">
<pre><code class="language-javascript">async function crearPublicacion(titulo, contenido, archivos = []) {
    const formData = new FormData();
    formData.append('usuario_nombre',      'Juan Pérez');
    formData.append('usuario_email',       'juan@empresa.com');
    formData.append('usuario_external_id', 'usr_001');
    formData.append('usuario_avatar',      'https://tusitio.com/avatars/juan.jpg');
    formData.append('titulo',              titulo);
    formData.append('contenido',           contenido);
    formData.append('tipo',                archivos.length > 0 ? 'multiple' : 'texto');

    archivos.forEach(archivo => formData.append('archivos[]', archivo));

    const res  = await fetch('http://TU_DOMINIO/ApiPublicacionesFB/publicaciones', {
        method: 'POST',
        headers: { 'X-API-KEY': 'tu_clave_api' },
        body: formData
    });
    return await res.json(); // { message: "...", post_id: 42 }
}</code></pre>
            </div>
            <div id="bash-post" class="tab-content">
<pre><code class="language-bash">curl -X POST http://TU_DOMINIO/ApiPublicacionesFB/publicaciones \
  -H "X-API-KEY: tu_clave_api" \
  -F "usuario_nombre=Juan Pérez" \
  -F "usuario_email=juan@empresa.com" \
  -F "usuario_external_id=usr_001" \
  -F "usuario_avatar=https://tusitio.com/avatars/juan.jpg" \
  -F "titulo=Mi publicación" \
  -F "contenido=<p>Texto aquí</p>" \
  -F "archivos[]=@/ruta/imagen.jpg"</code></pre>
            </div>

            <h3>Respuesta:</h3>
            <pre><code class="language-json">{ "message": "Publicación creada con éxito", "post_id": 42 }</code></pre>
        </div>

        <!-- ── DELETE ── -->
        <div class="doc-section" id="delete-post">
            <h2><i class="fas fa-trash" style="color:#ef4444;"></i> Eliminar Publicación</h2>
            <span class="method-badge method-delete">DELETE</span>
            <div class="endpoint-url">/publicaciones/{id}</div>
            <p>Elimina la publicación y todos sus archivos adjuntos del servidor.</p>
            <pre><code class="language-bash">curl -X DELETE http://TU_DOMINIO/ApiPublicacionesFB/publicaciones/42 \
  -H "X-API-KEY: tu_clave_api"</code></pre>
            <pre><code class="language-json">{ "message": "Publicación eliminada correctamente" }</code></pre>
        </div>

        <!-- ── ERRORS ── -->
        <div class="doc-section" id="response-format">
            <h2><i class="fas fa-exchange-alt" style="color:#8b5cf6;"></i> Errores HTTP</h2>
            <div class="param-table">
                <table>
                    <thead><tr><th>Código</th><th>Significado</th></tr></thead>
                    <tbody>
                        <tr><td><code>200</code></td><td>Éxito.</td></tr>
                        <tr><td><code>201</code></td><td>Recurso creado.</td></tr>
                        <tr><td><code>400</code></td><td>Datos incompletos o mal formados.</td></tr>
                        <tr><td><code>401</code></td><td>API Key inválida o ausente.</td></tr>
                        <tr><td><code>404</code></td><td>Publicación no encontrada.</td></tr>
                        <tr><td><code>500</code></td><td>Error interno del servidor.</td></tr>
                    </tbody>
                </table>
            </div>
            <pre><code class="language-json">{ "error": "API Key inválida o no proporcionada", "code": 401 }</code></pre>
        </div>

        <!-- ── USUARIOS Y AVATAR ── -->
        <div class="doc-section" id="usuarios">
            <h2><i class="fas fa-user-circle" style="color:#0ea5e9;"></i> Usuarios y Avatar</h2>
            <p>Cada publicación está asociada a un usuario. La API gestiona usuarios automáticamente: si el usuario no existe lo crea, y si ya existe lo <strong>actualiza con los datos más recientes</strong>.</p>

            <div class="alert-purple">
                <strong><i class="fas fa-thumbs-up"></i> Comportamiento del avatar:</strong><br>
                El campo <code>usuario_avatar</code> debe ser una <strong>URL pública</strong> de la imagen (no un archivo binario).<br>
                — En la <strong>primera publicación</strong> de un usuario → se guarda el avatar.<br>
                — En <strong>publicaciones posteriores</strong> del mismo usuario → el avatar se actualiza automáticamente con la URL nueva. Útil si el usuario cambia su foto de perfil en tu sitio.
            </div>

            <div class="param-table">
                <table>
                    <thead><tr><th>Campo</th><th>Cómo se identifica al usuario</th></tr></thead>
                    <tbody>
                        <tr><td><code>usuario_external_id</code></td><td>Recomendado. ID único del usuario en tu sistema (número de BD, UUID, etc.).</td></tr>
                        <tr><td><code>usuario_email</code></td><td>Alternativa si no tienes external_id. La búsqueda es por email.</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="alert-warn">
                <strong>Importante:</strong> Si no envías <code>usuario_external_id</code> ni <code>usuario_email</code>, se creará un usuario nuevo en cada publicación. Se recomienda siempre enviar al menos uno de los dos.
            </div>

            <h3>Ejemplo — primer login del usuario en tu sitio:</h3>
            <pre><code class="language-php">// En tu función de publicar (PHP):
$data = [
    'usuario_nombre'      => $currentUser->name,
    'usuario_email'       => $currentUser->email,
    'usuario_external_id' => (string) $currentUser->id,  // ← ID interno de tu BD
    'usuario_avatar'      => $currentUser->profilePhotoUrl(), // ← URL pública
    'contenido'           => $postContent,
];</code></pre>
        </div>

        <!-- ── PINNED ── -->
        <div class="doc-section" id="pinned">
            <h2><i class="fas fa-thumbtack" style="color:#d35400;"></i> Publicaciones Fijadas</h2>
            <p>Cuando se fija una publicación desde el panel, aparece <strong>siempre primero</strong> en el feed hasta que expire. El JSON incluye tres campos:</p>

            <div class="param-table">
                <table>
                    <thead><tr><th>Campo</th><th>Tipo</th><th>Descripción</th></tr></thead>
                    <tbody>
                        <tr><td><code>fijada</code></td><td>0 | 1</td><td>Si la publicación tiene activado el modo fijado.</td></tr>
                        <tr><td><code>fijada_hasta</code></td><td>Datetime | null</td><td>Fecha/hora de expiración. Si es <code>null</code>, está fijada permanentemente.</td></tr>
                        <tr><td><code>es_fijada_activa</code></td><td>0 | 1</td><td><strong>Calculado por MySQL.</strong> Vale <code>1</code> solo si la publicación está fijada <em>y</em> la fecha aún no ha expirado. Úsalo directamente en tu lógica.</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="alert-success">
                <strong>Recomendación:</strong> Usa directamente <code>es_fijada_activa</code> en tu código JavaScript para evitar problemas de zona horaria. Este campo lo calcula el servidor con <code>NOW()</code> de MySQL.
            </div>

            <pre><code class="language-javascript">// ✅ Forma recomendada — usa el campo precalculado:
const esFijado = post.es_fijada_activa == 1;

// ⚠️  Alternativa manual (puede tener diferencias de zona horaria):
function esFijadoManual(post) {
    if (post.fijada != 1) return false;
    if (!post.fijada_hasta) return true; // permanente
    return new Date(post.fijada_hasta) >= new Date();
}</code></pre>

            <h3>Estilo visual recomendado para posts fijados:</h3>
            <pre><code class="language-javascript">const card = document.createElement('div');
card.className = 'post-card' + (esFijado ? ' is-pinned' : '');
card.innerHTML = esFijado ? `
    &lt;div class="pinned-badge"&gt;
        &lt;i class="fas fa-thumbtack"&gt;&lt;/i&gt; Publicación Fijada
        ${post.fijada_hasta
            ? `&lt;span class="pin-until"&gt;&lt;i class="far fa-clock"&gt;&lt;/i&gt; hasta ${formatFecha(post.fijada_hasta)}&lt;/span&gt;`
            : ''}
    &lt;/div&gt;
` : '';</code></pre>
        </div>

        <!-- ── IMAGES ── -->
        <div class="doc-section" id="images">
            <h2><i class="fas fa-images" style="color:#7c3aed;"></i> Imágenes y Adjuntos</h2>
            <p>Las imágenes se <strong>optimizan automáticamente</strong> al subirse: se convierten a WebP y se redimensionan a 1200px máx de ancho.</p>

            <h3>Lógica de portada (imagen principal):</h3>
            <ol>
                <li>Si algún adjunto tiene <code>es_portada = 1</code> y es imagen → es la imagen principal.</li>
                <li>Si ninguna imagen está marcada como portada → usa la primera imagen del array.</li>
                <li>Las demás imágenes van como <strong>miniaturas</strong> debajo de la imagen principal (cuadrícula de hasta 4 columnas).</li>
                <li>Si hay más de 4 miniaturas, la cuarta muestra una capa oscura con <code>+N</code>.</li>
            </ol>

            <div class="alert-info">
                <strong>URL de un archivo:</strong><br>
                <code>http://TU_DOMINIO/ApiPublicacionesFB/uploads/<strong>{ruta_archivo}</strong></code>
            </div>
        </div>

        <!-- ── DOCUMENTS ── -->
        <div class="doc-section" id="documents">
            <h2><i class="fas fa-file-alt" style="color:#059669;"></i> Documentos</h2>
            <p>Los documentos son adjuntos que no son imágenes. Si el documento con <code>es_portada = 1</code> es un PDF, muéstralo en un visor embebido (<code>&lt;embed&gt;</code>). Para otros formatos, muestra el bloque de descarga.</p>

            <div class="param-table">
                <table>
                    <thead><tr><th>Extensión</th><th>Icono Font Awesome</th><th>Color</th></tr></thead>
                    <tbody>
                        <tr><td><code>pdf</code></td><td><code>fa-file-pdf</code></td><td>#ff4757</td></tr>
                        <tr><td><code>doc, docx</code></td><td><code>fa-file-word</code></td><td>#2e86de</td></tr>
                        <tr><td><code>xls, xlsx</code></td><td><code>fa-file-excel</code></td><td>#20bf6b</td></tr>
                        <tr><td><code>ppt, pptx</code></td><td><code>fa-file-powerpoint</code></td><td>#e84118</td></tr>
                        <tr><td><code>zip, rar</code></td><td><code>fa-file-archive</code></td><td>#8c7ae6</td></tr>
                        <tr><td><code>mp4, mov</code></td><td><code>fa-video</code></td><td>#ff4757</td></tr>
                        <tr><td><em>Otros</em></td><td><code>fa-file</code></td><td>#747d8c</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── RENDER FEED ── -->
        <div class="doc-section" id="render-feed">
            <h2><i class="fas fa-code" style="color:#0ea5e9;"></i> Renderizar el Feed (JavaScript)</h2>
            <p>Función completa lista para usar. Renderiza posts fijados, portada, miniaturas, PDFs, documentos adicionales y avatar.</p>

<?php
$js_render_code = <<<'EOD'
const API_URL = 'http://TU_DOMINIO/ApiPublicacionesFB/publicaciones';
const API_KEY = 'tu_clave_api';
const UPLOADS = 'http://TU_DOMINIO/ApiPublicacionesFB/uploads/';

async function cargarFeed() {
    const res   = await fetch(API_URL, { headers: { 'X-API-KEY': API_KEY } });
    const posts = await res.json();
    const feed  = document.getElementById('feed');

    posts.forEach(post => {
        // ── Clasificar adjuntos ──────────────────────
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

        if (!portada && imagenes.length)
            portada = { ...imagenes[0], tipo: 'imagen' };

        const miniaturas = imagenes.filter(i => !portada || i.id !== portada.id);

        // ── Construir tarjeta ────────────────────────
        const esFijado = post.es_fijada_activa == 1; // ← usa el campo precalculado
        const card = document.createElement('div');
        card.className = 'post-card' + (esFijado ? ' is-pinned' : '');
        card.innerHTML = `
            ${esFijado ? `
            <div class="pinned-badge">
                <i class="fas fa-thumbtack"></i> Publicación Fijada
                ${post.fijada_hasta
                    ? `<span class="pin-until"><i class="far fa-clock"></i> hasta ${formatFecha(post.fijada_hasta)}</span>`
                    : ''}
            </div>` : ''}

            <div class="post-header">
                ${post.usuario_avatar
                    ? `<img src="${post.usuario_avatar}" class="avatar-img" alt="${post.usuario_nombre}">`
                    : `<div class="avatar-placeholder">${post.usuario_nombre.charAt(0)}</div>`}
                <div>
                    <h4 class="author-name">${post.usuario_nombre}</h4>
                    <div class="post-meta">
                        <i class="far fa-clock"></i> ${formatFecha(post.created_at)}
                    </div>
                </div>
            </div>

            <div class="post-content-area">
                ${post.titulo ? `<h3 class="post-title">${post.titulo}</h3>` : ''}
                <div class="post-content">${post.contenido}</div>
            </div>

            ${renderVideo(post.video_url)}
            ${renderPortada(portada, miniaturas)}
            ${renderDocs(docs, portada)}
        `;
        feed.appendChild(card);
    });
}

// ── Helpers ─────────────────────────────────────
function formatFecha(dt) {
    return new Date(dt).toLocaleString('es-PE', {
        day:'2-digit', month:'2-digit', year:'numeric',
        hour:'2-digit', minute:'2-digit'
    });
}

function renderVideo(url) {
    if (!url) return '';
    
    // Si ya es un iframe, lo devolvemos tal cual
    if (url.includes('<iframe')) {
        return `<div class="video-container">${url}</div>`;
    }

    // Si es una URL de YouTube, la convertimos en embed
    let embedUrl = url;
    if (url.includes('youtube.com') || url.includes('youtu.be')) {
        const id = url.includes('v=') ? url.split('v=')[1].split('&')[0] : url.split('/').pop();
        embedUrl = `https://www.youtube.com/embed/${id}`;
        return `<div class="video-container">
            <iframe src="${embedUrl}" frameborder="0" allowfullscreen></iframe>
        </div>`;
    }

    return ''; 
}

function renderPortada(portada, miniaturas) {
    if (!portada) return '';
    const url = UPLOADS + portada.ruta_archivo;
    if (portada.tipo === 'imagen') {
        let html = `<div class="social-grid">
            <img src="${url}" class="featured-image" alt="Portada">`;
        if (miniaturas.length) {
            html += '<div class="thumbnails-row">';
            miniaturas.slice(0, 4).forEach((m, i) => {
                const mUrl = UPLOADS + m.ruta_archivo;
                html += `<div class="thumbnail-wrapper">
                    <img src="${mUrl}" class="thumbnail-item">
                    ${i === 3 && miniaturas.length > 4
                        ? `<div class="more-images-overlay">+${miniaturas.length - 3}</div>`
                        : ''}
                </div>`;
            });
            html += '</div>';
        }
        return html + '</div>';
    } else {
        const ext = portada.nombre_original.split('.').pop().toLowerCase();
        if (ext === 'pdf') {
            return `<div class="pdf-viewer-container">
                <div class="pdf-header">
                    <span><i class="fas fa-file-pdf"></i> ${portada.nombre_original}</span>
                    <a href="${url}" target="_blank"><i class="fas fa-download"></i></a>
                </div>
                <embed src="${url}" type="application/pdf" class="pdf-embed">
            </div>`;
        }
        return `<div class="fallback-doc">
            <i class="fas fa-file"></i>
            <p>${portada.nombre_original}</p>
            <a href="${url}" download class="btn-download"><i class="fas fa-download"></i> Descargar</a>
        </div>`;
    }
}

function renderDocs(docs, portada) {
    const adicionales = docs.filter(d => !portada || d.id !== portada.id);
    if (!adicionales.length) return '';
    const iconMap = {
        pdf:'fa-file-pdf', doc:'fa-file-word', docx:'fa-file-word',
        xls:'fa-file-excel', xlsx:'fa-file-excel',
        ppt:'fa-file-powerpoint', pptx:'fa-file-powerpoint',
        zip:'fa-file-archive', rar:'fa-file-archive',
        mp4:'fa-video', mov:'fa-video'
    };
    const items = adicionales.map(d => {
        const ext  = d.nombre_original.split('.').pop().toLowerCase();
        const icon = iconMap[ext] || 'fa-file';
        return `&lt;div class="doc-card"&gt;
            &lt;i class="fas ${icon}"&gt;&lt;/i&gt;
            &lt;span&gt;${d.nombre_original}&lt;/span&gt;
            &lt;a href="${UPLOADS + d.ruta_archivo}" download class="btn-download"&gt;
                &lt;i class="fas fa-download"&gt;&lt;/i&gt;
            &lt;/a&gt;
        &lt;/div&gt;`;
    }).join('');
    return `&lt;div class="docs-section"&gt;&lt;h4&gt;Documentos adjuntos&lt;/h4&gt;${items}&lt;/div&gt;`;
}

// Iniciar
cargarFeed();
EOD;
?>
            <pre><code class="language-javascript"><?php echo htmlspecialchars($js_render_code); ?></code></pre>
        </div>

        <!-- ── CSS ── -->
        <div class="doc-section" id="css-guide">
            <h2><i class="fas fa-palette" style="color:#ec4899;"></i> Estilos CSS Completos</h2>
            <p>Copia este CSS en tu web para replicar el aspecto visual del feed.</p>
            <pre><code class="language-css">/* ── Variables ───────────────────── */
:root {
    --primary: #0062ff;
    --radius:  14px;
    --shadow:  0 4px 12px rgba(0,0,0,0.06);
    --border:  #e5e7eb;
}

#feed { max-width: 760px; margin: 0 auto; padding: 1rem; }

/* ── Tarjeta ─────────────────────── */
.post-card {
    background: #fff;
    border-radius: var(--radius);
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    margin-bottom: 2rem;
    overflow: hidden;
    transition: transform 0.2s;
}
.post-card:hover { transform: translateY(-2px); }

/* ── Post Fijado ─────────────────── */
.post-card.is-pinned {
    border: 3px solid #ffd32a;
    background: linear-gradient(to bottom, #fffde7, white);
    box-shadow: 0 6px 24px rgba(255,211,42,0.35);
}
.pinned-badge {
    background: linear-gradient(135deg, #ffd32a, #f9a825);
    color: #5c3900;
    padding: 0.55rem 1.25rem;
    font-weight: 800; font-size: 0.82rem;
    display: flex; align-items: center; gap: 0.5rem;
}
.pinned-badge .pin-until { margin-left:auto; font-weight:500; font-size:0.75rem; opacity:0.8; }

/* ── Cabecera ────────────────────── */
.post-header   { padding: 1.25rem; display: flex; align-items: center; gap: 0.75rem; }
.avatar-img    { width:45px; height:45px; border-radius:50%; object-fit:cover; }
.avatar-placeholder {
    width:45px; height:45px; border-radius:50%;
    background: linear-gradient(135deg, #0062ff, #60a5fa);
    color:white; font-weight:700; font-size:1.2rem;
    display:flex; align-items:center; justify-content:center;
}
.author-name { font-weight:700; font-size:1rem; margin:0; }
.post-meta   { font-size:0.8rem; color:#6b7280; }

/* ── Contenido ───────────────────── */
.post-content-area { padding: 0 1.25rem 1.25rem; }
.post-title   { font-weight:800; font-size:1.15rem; margin-bottom:0.5rem; }
.post-content { font-size:1rem; line-height:1.7; color:#374151; }

/* ── Imagen principal ────────────── */
.social-grid    { display:flex; flex-direction:column; }
.featured-image { width:100%; max-height:500px; object-fit:contain; background:#f4f6f8; display:block; }

/* ── Miniaturas ──────────────────── */
.thumbnails-row { display:grid; grid-template-columns:repeat(4,1fr); gap:4px; padding:4px 0 0; }
.thumbnail-wrapper { position:relative; aspect-ratio:1/1; background:#f4f6f8; overflow:hidden; border-radius:6px; }
.thumbnail-item { width:100%; height:100%; object-fit:contain; cursor:pointer; }
.more-images-overlay {
    position:absolute; inset:0;
    background:rgba(0,0,0,0.6); color:white;
    font-weight:800; font-size:1.8rem;
    display:flex; align-items:center; justify-content:center;
}

/* ── PDF ─────────────────────────── */
.pdf-viewer-container { margin:0 1.25rem 1.25rem; border-radius:10px; overflow:hidden; border:1px solid #e5e7eb; }
.pdf-header { background:#1e293b; color:white; padding:0.75rem 1rem; display:flex; justify-content:space-between; align-items:center; font-size:0.85rem; }
.pdf-header a { color:white; }
.pdf-embed { width:100%; height:500px; border:none; display:block; }

/* ── Fallback documento ──────────── */
.fallback-doc { margin:0 1.25rem 1.25rem; border:1px solid #e5e7eb; border-radius:10px; padding:2rem; text-align:center; }
.fallback-doc i { font-size:3rem; color:#9ca3af; margin-bottom:1rem; }
.fallback-doc p { color:#6b7280; font-size:0.9rem; margin-bottom:1rem; }

/* ── Documentos adicionales ──────── */
.docs-section { padding:0 1.25rem 1.25rem; }
.docs-section h4 { font-size:0.75rem; text-transform:uppercase; color:#6b7280; letter-spacing:0.5px; margin-bottom:0.75rem; }
.doc-card { display:flex; align-items:center; gap:0.75rem; background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:0.75rem 1rem; margin-bottom:0.5rem; font-size:0.88rem; }
.doc-card i    { font-size:1.3rem; color:var(--primary); }
.doc-card span { flex:1; font-weight:500; }

/* ── Botón descarga ──────────────── */
.btn-download { display:inline-flex; align-items:center; gap:0.4rem; background:#eff6ff; color:var(--primary); padding:0.4rem 0.8rem; border-radius:8px; text-decoration:none; font-size:0.82rem; font-weight:600; }
.btn-download:hover { background:#dbeafe; }

/* ── Vídeo ────────────────────────── */
.video-container {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 */
    height: 0;
    overflow: hidden;
    margin: 0 1.25rem 1.25rem;
    border-radius: 12px;
    background: #000;
}
.video-container iframe, 
.video-container object, 
.video-container embed {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}
</code></pre>
        </div>

        <!-- ── EJEMPLO COMPLETO ── -->
        <div class="doc-section" id="examples">
            <h2><i class="fas fa-laptop-code" style="color:#3b82f6;"></i> Ejemplo HTML Completo</h2>
            <pre><code class="language-html">&lt;!DOCTYPE html&gt;
&lt;html lang="es"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;Feed de Publicaciones&lt;/title&gt;
    &lt;link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"&gt;
    &lt;style&gt;
        /* ── Pega aquí el CSS de la sección anterior ── */
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;div id="feed"&gt;&lt;/div&gt;
    &lt;script&gt;
    const API_URL = 'http://TU_DOMINIO/ApiPublicacionesFB/publicaciones';
    const API_KEY = 'tu_clave_api';
    const UPLOADS = 'http://TU_DOMINIO/ApiPublicacionesFB/uploads/';
    /* ── Pega aquí el JavaScript de la sección anterior ── */
    cargarFeed();
    &lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
        </div>

    </div><!-- /main -->
</div><!-- /layout -->

<script>
function showTab(id, btn) {
    const parent = btn.closest('.doc-section');
    parent.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    parent.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    btn.classList.add('active');
}

// Resaltar sección activa en sidebar
const sections  = document.querySelectorAll('.doc-section');
const navLinks  = document.querySelectorAll('.docs-sidebar a');
window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(s => { if (window.scrollY >= s.offsetTop - 80) current = s.id; });
    navLinks.forEach(a => {
        a.classList.remove('active');
        if (a.getAttribute('href') === '#' + current) a.classList.add('active');
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
