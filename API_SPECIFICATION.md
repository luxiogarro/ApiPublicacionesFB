# API de Publicaciones — Guía de Integración Completa para IA

> **Cómo usar este archivo:** Cópialo a la raíz de tu proyecto y dile a la IA:  
> *"Lee `ia-spec.md` e implementa el feed de publicaciones exactamente como describe, usando la API KEY y URL base indicadas."*

---

## 1. Datos de Conexión

```
URL Base:    https://apipubli.luxio.dev/
API Key:     [obtener en Admin → Clientes & API Keys]
Uploads URL: https://apipubli.luxio.dev/uploads/
```

Autenticación: **todas las peticiones** deben incluir el header:
```
X-API-KEY: TU_API_KEY
```

---

## 2. Seguridad y Multi-tenancy (Aislamiento)
Este sistema aplica un aislamiento estricto por cliente. Tu `X-API-KEY` determina automáticamente qué datos puedes ver y modificar.
- **Aislamiento de Lectura**: Al consultar `/publicaciones`, solo recibirás las de tu propia empresa.
- **Aislamiento de Escritura**: Al crear, actualizar o borrar, el sistema verifica que la operación pertenezca a tu `cliente_id`.
- **Usuarios Locales**: Los autores son locales a tu instancia. Si envías un `usuario_external_id` que ya existe en otra empresa, para tu sistema será un usuario nuevo y único.

---

## 3. Endpoints de la API

### 2.1 Obtener Feed de Publicaciones
```
GET https://apipubli.luxio.dev/publicaciones
```

**Query Params opcionales:**
| Param | Tipo | Descripción |
|---|---|---|
| `limit` | int | Cantidad de resultados (default 10) |
| `offset` | int | Desplazamiento para paginación/infinite scroll |
| `search` | string | Búsqueda en título y contenido |
| `cliente_id` | int | Filtrar por empresa específica |
| `fecha_inicio` | YYYY-MM-DD | Rango de fecha desde |
| `fecha_fin` | YYYY-MM-DD | Rango de fecha hasta |

**Lógica de ordenamiento (precalculada por el servidor):**
1. Posts con `es_fijada_activa = 1` siempre al inicio
2. Luego el resto por `created_at DESC`

### 2.2 Crear Publicación
```
POST https://apipubli.luxio.dev/publicaciones
Content-Type: multipart/form-data
```

**Campos:**
| Campo | Tipo | Descripción |
|---|---|---|
| `usuario_nombre` | string | Nombre del autor |
| `usuario_external_id` | string | ID del usuario en tu sistema |
| `usuario_avatar` | string | URL de la foto de perfil |
| `titulo` | string | Opcional. Puede contener HTML |
| `contenido` | string | HTML permitido |
| `video_url` | string | URL YouTube/Vimeo o código `<iframe>` completo |
| `fijada` | int 0\|1 | Si la publicación debe quedar anclada en el tope |
| `fijada_hasta` | datetime | Fecha límite del anclaje (formato: `2026-12-31 23:59:00`) |
| `archivos[]` | File[] | Array de archivos (imágenes JPG/PNG/WEBP, PDFs, docs) |
| `es_portada` | int | Índice (0-based) del archivo que será la imagen principal |

### 2.3 Actualizar Publicación
```
PUT https://apipubli.luxio.dev/publicaciones/{id}
Content-Type: application/json
```
**Body:** (Mismos campos que el POST, excepto archivos)

### 2.4 Eliminar Publicación
```
DELETE https://apipubli.luxio.dev/publicaciones/{id}
```

---

## 3. Estructura del JSON de Respuesta

```json
{
  "id": 100,
  "titulo": "Título de la Noticia",
  "contenido": "<p>Contenido rico en HTML...</p>",
  "video_url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
  "tipo": "multiple",
  "created_at": "2026-02-23 12:00:00",
  "usuario_nombre": "María García",
  "usuario_avatar": "https://example.com/avatar.jpg",
  "es_fijada_activa": 1,
  "fijada_hasta": "2026-03-01 23:59:00",
  "cliente_nombre": "Empresa XYZ",
  "adjuntos": [
    {
      "id": 50,
      "ruta_archivo": "uuid-generado.webp",
      "nombre_original": "foto-evento.png",
      "es_portada": 1
    },
    {
      "id": 51,
      "ruta_archivo": "uuid-generado-2.webp",
      "nombre_original": "detalle.jpg",
      "es_portada": 0
    },
    {
      "id": 52,
      "ruta_archivo": "documento.pdf",
      "nombre_original": "informe-Q1.pdf",
      "es_portada": 0
    }
  ]
}
```

**Lógica de adjuntos:**
- **Imágenes** = extensiones: `jpg`, `jpeg`, `png`, `gif`, `webp`
- **Documentos** = todo lo demás (pdf, docx, xlsx, etc.)
- El adjunto con `es_portada: 1` es el elemento principal visible
- Si ninguno tiene `es_portada: 1`, usar la primera imagen como portada
- La URL completa de un archivo es: `UPLOADS_URL + adjunto.ruta_archivo`

---

## 4. Implementación Frontend Completa

### 4.1 HTML base de la página

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed de Publicaciones</title>
    <!-- FontAwesome (iconos) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Fancybox (galería de imágenes con zoom) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css">
    <style>
        /* === PEGAR EL CSS DE LA SECCIÓN 4.2 AQUÍ === */
    </style>
</head>
<body>
    <div class="feed-container" id="feed-container">
        <div id="feed-items">
            <!-- Las tarjetas se insertan aquí por JS -->
        </div>
        <!-- Infinite scroll sentinel -->
        <div id="scroll-sentinel" style="height:50px; display:flex; align-items:center; justify-content:center; margin-top:2rem;">
            <div id="loader-spinner" style="display:none;">
                <i class="fas fa-spinner fa-spin" style="font-size:1.5rem; color:#0062ff;"></i>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
    <script>
        /* === PEGAR EL JS DE LA SECCIÓN 4.3 AQUÍ === */
    </script>
</body>
</html>
```

---

### 4.2 CSS Completo del Feed

```css
/* =============================================
   FEED DE PUBLICACIONES — CSS COMPLETO
   Replica exacta del Monitor Feed de Admin
   ============================================= */

* { box-sizing: border-box; }

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: #f4f6fb;
    color: #1a1a2e;
    margin: 0;
    padding: 2rem 1rem;
}

.feed-container {
    max-width: 800px;
    margin: 0 auto;
}

/* ── Tarjeta base ── */
.post-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
    border: 1px solid rgba(0,0,0,0.05);
    overflow: hidden;
    transition: transform 0.2s ease;
}
.post-card:hover { transform: translateY(-2px); }

/* ── Publicación Fijada ── */
.post-card.is-pinned {
    border: 3px solid #ffd32a;
    background: linear-gradient(to bottom, #fffde7, white);
    box-shadow: 0 6px 24px rgba(255, 211, 42, 0.35);
}
.pinned-badge {
    background: linear-gradient(135deg, #ffd32a, #f9a825);
    color: #5c3900;
    padding: 0.55rem 1.25rem;
    font-weight: 800;
    font-size: 0.82rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    letter-spacing: 0.3px;
}
.pin-until {
    font-weight: 500;
    font-size: 0.75rem;
    opacity: 0.8;
    margin-left: auto;
}

/* ── Header de la tarjeta (autor + fecha) ── */
.post-header {
    padding: 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}
.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.avatar-img {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.avatar-placeholder {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #0062ff, #60a5fa);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.2rem;
    box-shadow: 0 4px 10px rgba(0,98,255,0.2);
}
.author-name {
    font-weight: 700;
    font-size: 1rem;
    color: #1a1a2e;
    margin: 0;
}
.post-meta {
    font-size: 0.8rem;
    color: #747d8c;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 2px;
}
.client-tag {
    background: #e7f3ff;
    color: #0062ff;
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.7rem;
}

/* ── Contenido de texto ── */
.post-content-container { padding: 0 1.25rem 1.25rem; }
.post-title {
    font-weight: 800;
    font-size: 1.15rem;
    margin: 0 0 0.5rem;
    color: #1a1a2e;
}
.post-content-text {
    font-size: 1.05rem;
    line-height: 1.6;
    color: #333;
    max-height: 6.5em;
    overflow: hidden;
    transition: max-height 0.4s ease;
}
.post-content-text.expanded { max-height: 9999px; }
.read-more-btn {
    background: none;
    border: none;
    color: #0062ff;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    padding: 0;
    margin-top: 0.25rem;
    display: none; /* se muestra con JS si hay overflow */
}

/* ── Video embebido (YouTube / Vimeo / Facebook) ── */
.video-container {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 */
    height: 0;
    overflow: hidden;
    border-radius: 12px;
    margin: 0 1.25rem 1.25rem;
    background: #000;
}
.video-container iframe,
.video-container object,
.video-container embed {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    border: 0;
}

/* ── Galería de imágenes (estilo social media) ── */
.social-grid {
    display: flex;
    flex-direction: column;
}
.featured-link { display: block; }
.featured-image {
    width: 100%;
    max-height: 500px;
    object-fit: contain;
    background: #f4f6fb;
    display: block;
}
.thumbnails-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 4px;
    margin-top: 4px;
}
.thumbnail-wrapper {
    position: relative;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    border-radius: 6px;
    display: block;
}
.thumbnail-item {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.more-images-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.55);
    color: white;
    font-weight: 800;
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
}

/* ── PDF o documento destacado ── */
.pdf-viewer-container {
    margin: 0 1.25rem 1.25rem;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}
.pdf-header {
    background: #f8f9fa;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
    border-bottom: 1px solid #e5e7eb;
}
.pdf-header i { color: #ef4444; }
.pdf-embed {
    width: 100%;
    height: 450px;
    display: block;
    border: none;
}

/* ── Documentos adicionales ── */
.additional-docs-section {
    padding: 0 1.25rem 1.25rem;
}
.additional-docs-section .section-title {
    font-size: 0.75rem;
    font-weight: 700;
    color: #747d8c;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 0.5rem;
}
.docs-column { display: flex; flex-direction: column; gap: 0.5rem; }
.doc-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8f9fa;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 0.6rem 1rem;
}
.doc-filename { font-size: 0.85rem; color: #374151; word-break: break-all; }
.download-link-icon {
    color: #0062ff;
    font-size: 1rem;
    flex-shrink: 0;
    margin-left: 0.5rem;
    text-decoration: none;
}
```

---

### 4.3 JavaScript Completo del Feed

```javascript
/* =============================================
   FEED DE PUBLICACIONES — JS COMPLETO
   Reemplaza TU_API_KEY y TU_DOMINIO
   ============================================= */

const API_URL   = 'https://TU_DOMINIO/publicaciones';
const API_KEY   = 'TU_API_KEY';
const UPLOADS   = 'https://TU_DOMINIO/uploads/';
const IMG_EXTS  = ['jpg','jpeg','png','gif','webp'];

let offset    = 0;
let limit     = 10;
let loading   = false;
let allLoaded = false;

// ── Iniciar al cargar la página ──
document.addEventListener('DOMContentLoaded', function() {
    loadPosts();     // Carga inicial
    setupScroll();   // Infinite scroll
});

// ── Carga principal (y recarga de filtros) ──
async function loadPosts(reset) {
    if (reset) { offset = 0; allLoaded = false; document.getElementById('feed-items').innerHTML = ''; }
    if (loading || allLoaded) return;
    loading = true;
    showSpinner(true);

    var params = new URLSearchParams({ limit: limit, offset: offset });
    // Agrega filtros activos si los tienes en variables:
    // if (searchQuery) params.set('search', searchQuery);
    // if (clienteId)   params.set('cliente_id', clienteId);

    try {
        var res   = await fetch(API_URL + '?' + params.toString(), {
            headers: { 'X-API-KEY': API_KEY }
        });
        var posts = await res.json();

        if (!Array.isArray(posts) || posts.length === 0) {
            allLoaded = true;
            if (offset === 0) {
                document.getElementById('feed-items').innerHTML =
                    '<p style="text-align:center;color:#999;padding:3rem">No hay publicaciones aún.</p>';
            } else {
                document.getElementById('scroll-sentinel').innerHTML =
                    '<p style="color:#747d8c;font-size:.9rem">No hay más publicaciones.</p>';
            }
            return;
        }

        var container = document.getElementById('feed-items');
        posts.forEach(function(post) {
            container.insertAdjacentHTML('beforeend', buildCard(post));
        });
        offset += posts.length;

        // Re-inicializar Fancybox para las nuevas tarjetas
        if (typeof Fancybox !== 'undefined') Fancybox.bind('[data-fancybox]');

        // Activar botones "Ver más" si el contenido está recortado
        activateReadMoreButtons();

    } catch(e) {
        console.error('Error al cargar el feed:', e);
        document.getElementById('feed-items').innerHTML =
            '<p style="color:red;padding:2rem">Error al conectar con la API: ' + e.message + '</p>';
    } finally {
        loading = false;
        showSpinner(false);
    }
}

// ── Constructor de la tarjeta HTML ──
function buildCard(post) {
    var adjuntos  = post.adjuntos || [];
    var imagenes  = [];
    var docs      = [];
    var portadaImg = null;
    var portadaDoc = null;

    // Separar imágenes de documentos y detectar portada
    adjuntos.forEach(function(a) {
        var ext = a.nombre_original.split('.').pop().toLowerCase();
        if (IMG_EXTS.indexOf(ext) >= 0) {
            imagenes.push(a);
            if (a.es_portada == 1) portadaImg = a;
        } else {
            docs.push(a);
            if (a.es_portada == 1) portadaDoc = a;
        }
    });
    if (!portadaImg && imagenes.length > 0) portadaImg = imagenes[0];

    var miniaturas = imagenes.filter(function(i) {
        return !portadaImg || i.id !== portadaImg.id;
    });

    var isFijado = post.es_fijada_activa == 1;
    var html = '';

    // Apertura de tarjeta
    html += '<div class="post-card' + (isFijado ? ' is-pinned' : '') + '" data-id="' + post.id + '">';

    // Badge de fijado
    if (isFijado) {
        html += '<div class="pinned-badge">';
        html += '<i class="fas fa-thumbtack"></i> Publicación Fijada';
        if (post.fijada_hasta) {
            var d = new Date(post.fijada_hasta);
            html += '<span class="pin-until"><i class="far fa-clock"></i> hasta ' + d.toLocaleDateString('es-PE') + '</span>';
        }
        html += '</div>';
    }

    // Header (autor + fecha)
    html += '<div class="post-header"><div class="user-info">';
    if (post.usuario_avatar) {
        html += '<img src="' + post.usuario_avatar + '" class="avatar-img" alt="' + escHtml(post.usuario_nombre) + '">';
    } else {
        html += '<div class="avatar-placeholder">' + post.usuario_nombre.charAt(0).toUpperCase() + '</div>';
    }
    html += '<div><h4 class="author-name">' + escHtml(post.usuario_nombre) + '</h4>';
    html += '<div class="post-meta">';
    if (post.cliente_nombre) {
        html += '<span class="client-tag"><i class="fas fa-building"></i> ' + escHtml(post.cliente_nombre) + '</span> •';
    }
    html += '<span><i class="far fa-clock"></i> ' + formatDate(post.created_at) + '</span>';
    html += '</div></div></div></div>'; // cierra user-info, post-header

    // Título + contenido
    html += '<div class="post-content-container">';
    if (post.titulo) {
        html += '<h3 class="post-title">' + escHtml(post.titulo) + '</h3>';
    }
    html += '<div class="post-content-text" id="content-' + post.id + '">' + post.contenido + '</div>';
    html += '<button class="read-more-btn" id="btn-' + post.id + '" onclick="toggleContent(' + post.id + ')">Ver más...</button>';
    html += '</div>';

    // Video embebido
    if (post.video_url) {
        var iframe = buildVideoEmbed(post.video_url);
        if (iframe) html += '<div class="video-container">' + iframe + '</div>';
    }

    // Galería de imágenes
    if (portadaImg) {
        html += '<div class="social-grid">';
        html += '<a href="' + UPLOADS + portadaImg.ruta_archivo + '" data-fancybox="gallery-' + post.id + '" class="featured-link">';
        html += '<img src="' + UPLOADS + portadaImg.ruta_archivo + '" class="featured-image" alt="Portada">';
        html += '</a>';
        if (miniaturas.length > 0) {
            html += '<div class="thumbnails-row">';
            miniaturas.forEach(function(m, idx) {
                if (idx < 3) {
                    html += '<a href="' + UPLOADS + m.ruta_archivo + '" data-fancybox="gallery-' + post.id + '" class="thumbnail-wrapper">';
                    html += '<img src="' + UPLOADS + m.ruta_archivo + '" class="thumbnail-item" alt="">';
                    html += '</a>';
                } else if (idx === 3) {
                    html += '<a href="' + UPLOADS + m.ruta_archivo + '" data-fancybox="gallery-' + post.id + '" class="thumbnail-wrapper">';
                    html += '<img src="' + UPLOADS + m.ruta_archivo + '" class="thumbnail-item" alt="">';
                    if (miniaturas.length > 4) {
                        html += '<div class="more-images-overlay">+' + (miniaturas.length - 3) + '</div>';
                    }
                    html += '</a>';
                } else {
                    // imágenes ocultas extras para fancybox
                    html += '<a href="' + UPLOADS + m.ruta_archivo + '" data-fancybox="gallery-' + post.id + '" style="display:none"></a>';
                }
            });
            html += '</div>';
        }
        html += '</div>'; // social-grid
    }

    // Documento destacado como portada
    if (portadaDoc) {
        var extDoc = portadaDoc.nombre_original.split('.').pop().toLowerCase();
        html += '<div class="pdf-viewer-container">';
        if (extDoc === 'pdf') {
            html += '<div class="pdf-header"><i class="fas fa-file-pdf"></i><span>' + escHtml(portadaDoc.nombre_original) + '</span></div>';
            html += '<embed src="' + UPLOADS + portadaDoc.ruta_archivo + '" type="application/pdf" class="pdf-embed">';
        } else {
            html += '<div style="padding:2rem;text-align:center">';
            html += '<i class="fas fa-file-alt" style="font-size:3rem;color:#747d8c;margin-bottom:1rem"></i>';
            html += '<h4>' + escHtml(portadaDoc.nombre_original) + '</h4>';
            html += '<a href="' + UPLOADS + portadaDoc.ruta_archivo + '" target="_blank" style="color:#0062ff">Descargar archivo</a>';
            html += '</div>';
        }
        html += '</div>';
    }

    // Documentos adicionales
    if (docs.length > 0) {
        html += '<div class="additional-docs-section">';
        html += '<span class="section-title">Documentos Adicionales</span>';
        html += '<div class="docs-column">';
        docs.forEach(function(d) {
            html += '<div class="doc-card">';
            html += '<span class="doc-filename"><i class="fas fa-file"></i> ' + escHtml(d.nombre_original) + '</span>';
            html += '<a href="' + UPLOADS + d.ruta_archivo + '" target="_blank" class="download-link-icon"><i class="fas fa-download"></i></a>';
            html += '</div>';
        });
        html += '</div></div>';
    }

    html += '</div>'; // cierra post-card
    return html;
}

// ── Generador de embed para videos ──
function buildVideoEmbed(url) {
    if (!url) return null;
    // Si ya es un iframe completo, usarlo tal cual
    if (url.indexOf('<iframe') >= 0) return url;
    // YouTube
    var ytMatch = url.match(/(?:youtube(?:-nocookie)?\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i);
    if (ytMatch) return '<iframe src="https://www.youtube.com/embed/' + ytMatch[1] + '" allowfullscreen></iframe>';
    // Vimeo
    var vmMatch = url.match(/vimeo\.com\/(?:video\/)?(\d+)/i);
    if (vmMatch) return '<iframe src="https://player.vimeo.com/video/' + vmMatch[1] + '" allowfullscreen></iframe>';
    // Facebook
    if (url.indexOf('facebook.com') >= 0) {
        return '<iframe src="https://www.facebook.com/plugins/video.php?href=' + encodeURIComponent(url) + '&show_text=0&width=560" style="border:none;overflow:hidden" scrolling="no" allowfullscreen></iframe>';
    }
    return null;
}

// ── Infinite Scroll con IntersectionObserver ──
function setupScroll() {
    var sentinel = document.getElementById('scroll-sentinel');
    var observer = new IntersectionObserver(function(entries) {
        if (entries[0].isIntersecting && !loading && !allLoaded) {
            loadPosts();
        }
    }, { threshold: 0.1 });
    observer.observe(sentinel);
}

// ── Botón "Ver más" / colapsar texto largo ──
function activateReadMoreButtons() {
    document.querySelectorAll('.post-content-text').forEach(function(el) {
        var id  = el.id.replace('content-', '');
        var btn = document.getElementById('btn-' + id);
        if (btn && el.scrollHeight > el.offsetHeight + 5) {
            btn.style.display = 'inline-block';
        }
    });
}

function toggleContent(id) {
    var el  = document.getElementById('content-' + id);
    var btn = document.getElementById('btn-' + id);
    if (!el) return;
    if (el.classList.contains('expanded')) {
        el.classList.remove('expanded');
        btn.innerText = 'Ver más...';
    } else {
        el.classList.add('expanded');
        btn.innerText = 'Ver menos';
    }
}

// ── Helpers ──
function showSpinner(show) {
    var s = document.getElementById('loader-spinner');
    if (s) s.style.display = show ? 'block' : 'none';
}

function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function formatDate(dateStr) {
    try {
        return new Date(dateStr).toLocaleDateString('es-PE', {
            day: '2-digit', month: '2-digit', year: 'numeric', hour:'2-digit', minute:'2-digit'
        });
    } catch(e) { return dateStr; }
}

// Inicializar Fancybox con la configuración del panel admin
if (typeof Fancybox !== 'undefined') {
    Fancybox.bind('[data-fancybox]', {
        Images: { zoom: false },
        Toolbar: { display: { left:[], middle:[], right:['close'] } }
    });
}
```

---

## 5. Instrucciones específicas para la IA que integra este feed

```
1. Usa el CSS y JS de las secciones 4.2 y 4.3 TAL CUAL, solo cambia API_URL, API_KEY y UPLOADS.
2. La galería de imágenes funciona con Fancybox v5. NO la reemplaces con otra librería.
3. Los posts fijados (es_fijada_activa=1) deben tener la clase CSS "is-pinned" para mostrarse con borde dorado.
4. El texto del contenido puede contener HTML — renderizarlo como innerHTML, NO como texto plano.
5. El botón "Ver más" se activa automáticamente cuando el texto supera 4 líneas visibles.
6. El infinite scroll usa IntersectionObserver sobre el elemento #scroll-sentinel. Incrementa el offset en cada carga.
7. Si hay portadaImg: mostrar imagen principal grande + miniaturas debajo (máx 4 visibles, overlay "+N" si hay más).
8. Si hay portadaDoc PDF: mostrar <embed> incrustado con altura 450px.
9. Para videos: convertir la URL a iframe embed. Soporta YouTube, Vimeo y Facebook.
10. Cada galería tiene su propio grupo de Fancybox usando data-fancybox="gallery-{post.id}" para separar galerías de diferentes posts.
```
