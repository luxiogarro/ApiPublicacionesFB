<?php
$page = 'nueva_p';
$pageTitle = 'Crear Nueva Publicación';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/autoload.php';
require_once __DIR__ . '/includes/header.php';

use App\Core\Database;
use App\Model\Post;
use App\Model\User;

$db = Database::getInstance()->getConnection();
$message = '';
$error = '';

// Obtener clientes para el selector
$clientes = $db->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = $_POST['cliente_id'];
    $titulo = $_POST['titulo'] ?? '';
    $contenido = $_POST['contenido'];
    $video_url = $_POST['video_url'] ?? null;
    $usuario_nombre = $_POST['usuario_nombre'] ?: 'Admin Global';
    
    // Nueva lógica de destaque unificado
    $tipo_destacado = $_POST['tipo_destacado'] ?? 'image'; // 'image' o 'doc'
    $destacado_index = isset($_POST['destacado_index']) ? (int)$_POST['destacado_index'] : 0;
    
    // Lógica Fijado
    $fijada = isset($_POST['fijada']) ? 1 : 0;
    $fijada_hasta = !empty($_POST['fijada_hasta']) ? $_POST['fijada_hasta'] : null;

    if ($cliente_id && $contenido) {
        try {
            $usuario_id = User::getOrCreate($cliente_id, [
                'nombre' => $usuario_nombre,
                'external_id' => 'admin_panel',
                'avatar' => 'https://ui-avatars.com/api/?name=Admin&background=0062ff&color=fff'
            ]);

            $post_id = Post::create([
                'usuario_id' => $usuario_id,
                'cliente_id' => $cliente_id,
                'titulo' => $titulo,
                'contenido' => $contenido,
                'video_url' => $video_url,
                'tipo' => 'texto',
                'fijada' => $fijada,
                'fijada_hasta' => $fijada_hasta
            ]);

            $has_multimedia = false;

            // 1. Procesar IMÁGENES
            if (!empty($_FILES['imagenes']['name'][0])) {
                $imgs = $_FILES['imagenes'];
                for ($i = 0; $i < count($imgs['name']); $i++) {
                    if ($imgs['error'][$i] === UPLOAD_ERR_OK) {
                        $optimizedName = \App\Core\ImageOptimizer::optimize($imgs['tmp_name'][$i], $imgs['name'][$i]);
                        if ($optimizedName) {
                            $es_portada = ($tipo_destacado === 'image' && $i === $destacado_index) ? 1 : 0;
                            Post::addAttachment($post_id, [
                                'ruta' => $optimizedName,
                                'tipo' => 'image/webp',
                                'nombre' => $imgs['name'][$i]
                            ], $es_portada);
                            $has_multimedia = true;
                        }
                    }
                }
            }

            // 2. Procesar DOCUMENTOS
            if (!empty($_FILES['documentos']['name'][0])) {
                $docs = $_FILES['documentos'];
                for ($i = 0; $i < count($docs['name']); $i++) {
                    if ($docs['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($docs['name'][$i], PATHINFO_EXTENSION);
                        $filename = uniqid() . '.' . $ext;
                        if (move_uploaded_file($docs['tmp_name'][$i], UPLOAD_DIR . $filename)) {
                            $es_destacado = ($tipo_destacado === 'doc' && $i === $destacado_index) ? 1 : 0;
                            Post::addAttachment($post_id, [
                                'ruta' => $filename,
                                'tipo' => $docs['type'][$i],
                                'nombre' => $docs['name'][$i]
                            ], $es_destacado);
                            $has_multimedia = true;
                        }
                    }
                }
            }
            
            if ($has_multimedia) {
                $db->prepare("UPDATE publicaciones SET tipo = 'multiple' WHERE id = ?")->execute([$post_id]);
            }

            $message = "¡Publicación creada con éxito!";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Selecciona un cliente y escribe un contenido.";
    }
}
?>

<style>
    .form-container { max-width: 900px; margin: 0 auto; }
    .upload-sections { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
    .upload-box { 
        background: white; border: 2px dashed #e0e0e0; border-radius: var(--radius); padding: 1.5rem;
        text-align: center; cursor: pointer; transition: var(--transition);
    }
    .upload-box:hover { border-color: var(--primary); background: #f0f7ff; }
    .upload-box i { font-size: 2rem; color: var(--primary); margin-bottom: 0.5rem; display: block; }
    
    .preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; margin-top: 1rem; }
    .preview-item { 
        position: relative; border-radius: 8px; overflow: hidden; height: 100px; border: 2px solid transparent; 
        transition: var(--transition);
    }
    .preview-item img { width: 100%; height: 100%; object-fit: cover; }
    .preview-item.is-cover { border-color: var(--success); box-shadow: 0 0 10px rgba(0, 200, 83, 0.3); }
    .cover-badge { 
        position: absolute; top: 5px; right: 5px; background: var(--success); color: white;
        font-size: 0.6rem; padding: 2px 5px; border-radius: 4px; font-weight: 700;
        display: none;
    }
    .preview-item.is-cover .cover-badge { display: block; }
    
    .file-list { text-align: left; margin-top: 1rem; font-size: 0.85rem; }
    .file-item { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.3rem; padding: 5px; background: #f8f9fa; border-radius: 5px; }
</style>

<div class="admin-header">
    <h1>Nueva Publicación</h1>
    <p style="color: var(--text-muted);">Gestiona imágenes y archivos de forma profesional.</p>
</div>

<?php if ($message): ?>
<div class="card" style="background: var(--success); color: white; padding: 1rem; margin-bottom: 2rem;">
    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
</div>
<?php endif; ?>

<div class="form-container">
    <form method="POST" enctype="multipart/form-data" id="publish-form">
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="input-group">
                <label>Seleccionar Cliente</label>
                <select name="cliente_id" required>
                    <option value="">-- Selecciona un sitio web --</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group" style="margin-top:1rem;">
                <label>Autor (Opcional)</label>
                <input type="text" name="usuario_nombre" value="Admin Global">
            </div>
            <div class="input-group" style="margin-top:1rem;">
                <label>Título de la Publicación (Negrita)</label>
                <input type="text" name="titulo" placeholder="Ej: ¡Gran Webinar este Viernes!" style="font-weight: 700;">
            </div>
            <!-- Video Embebido -->
            <div class="card" style="margin-top:1rem; margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #ffe3e3; color: #ff4757; display: flex; align-items: center; justify-content: center;">
                        <i class="fab fa-youtube"></i>
                    </div>
                    <h3 style="font-size: 1rem; font-weight: 700;">Video Embebido (Opcional)</h3>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                        Pega una URL de YouTube, Vimeo, Facebook o un código <code>&lt;iframe&gt;</code>.
                    </p>
                    <input type="text" name="video_url" class="form-control" placeholder="Ej: https://www.youtube.com/watch?v=...">
                </div>
            </div>
            <div class="input-group" style="margin-top:1rem;">
                <label>Contenido Enriquecido</label>
                <textarea name="contenido" id="tiny-editor" rows="10" placeholder="¿Qué quieres compartir?"></textarea>
            </div>
            <div class="input-group" style="margin-top:1rem; display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #fffcf2; border-radius:8px; border:1px solid #ffeaa7;">
                <label style="margin:0; display:flex; align-items:center; gap: 0.5rem; cursor:pointer;">
                    <input type="checkbox" name="fijada" value="1" id="fijar-checkbox" onchange="toggleFijarDate()" style="width:18px; height:18px;">
                    <strong><i class="fas fa-thumbtack" style="color:#d35400;"></i> Fijar en la parte superior del Feed</strong>
                </label>
                <div id="fijar-date-container" style="display:none; flex: 1;">
                    <input type="datetime-local" name="fijada_hasta" style="padding: 0.6rem; border-radius:6px; border:1px solid #ddd; width:100%;">
                </div>
            </div>
        </div>

        <div class="upload-sections">
            <!-- Sección Imágenes -->
            <div class="card">
                <h3 style="font-size: 1rem; margin-bottom: 1rem;"><i class="fas fa-images"></i> Imágenes</h3>
                <div class="upload-box" onclick="document.getElementById('input-imgs').click()">
                    <i class="fas fa-camera"></i>
                    <span>Subir Fotos</span>
                    <input type="file" name="imagenes[]" id="input-imgs" multiple accept="image/*" style="display:none" onchange="previewImages()">
                </div>
                <!-- Inputs de Destaque Unificado -->
                <input type="hidden" name="tipo_destacado" id="tipo_destacado" value="image">
                <input type="hidden" name="destacado_index" id="destacado_index" value="0">
                
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">Haz clic en una imagen o archivo para marcarlo como <strong>Destacado</strong>.</p>
                <div id="imgs-preview" class="preview-grid"></div>
            </div>

            <!-- Sección Archivos -->
            <div class="card">
                <h3 style="font-size: 1rem; margin-bottom: 1rem;"><i class="fas fa-file-export"></i> Documentos y Otros</h3>
                <div class="upload-box" onclick="document.getElementById('input-docs').click()">
                    <i class="fas fa-file-upload"></i>
                    <span>Subir Archivos</span>
                    <input type="file" name="documentos[]" id="input-docs" multiple style="display:none" onchange="listDocs()">
                </div>
                <div id="docs-list" class="file-list"></div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
            <i class="fas fa-paper-plane"></i> Publicar Ahora
        </button>
    </form>
</div>

<script>
let allImages = [];
let allDocs = [];

function toggleFijarDate() {
    const checkbox = document.getElementById('fijar-checkbox');
    const container = document.getElementById('fijar-date-container');
    container.style.display = checkbox.checked ? 'block' : 'none';
}

function previewImages() {
    const preview = document.getElementById('imgs-preview');
    const input = document.getElementById('input-imgs');
    const tipoDest = document.getElementById('tipo_destacado');
    const indexDest = document.getElementById('destacado_index');
    
    if (input.files.length > 0) {
        Array.from(input.files).forEach(file => {
            allImages.push(file);
        });
        // Limpiar el input para permitir re-selección manual si fuera necesario
        input.value = "";
    }
    
    renderImagesPreview();
}

function renderImagesPreview() {
    const preview = document.getElementById('imgs-preview');
    const tipoDest = document.getElementById('tipo_destacado');
    const indexDest = document.getElementById('destacado_index');
    preview.innerHTML = '';

    allImages.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'preview-item' + (tipoDest.value === 'image' && index == indexDest.value ? ' is-cover' : '');
            div.innerHTML = `
                <img src="${e.target.result}">
                <span class="cover-badge">PORTADA</span>
                <button type="button" class="remove-btn" onclick="removeFile('image', ${index}, event)">&times;</button>
                <div class="click-area" onclick="setFeatured('image', ${index})"></div>
            `;
            preview.appendChild(div);
        }
        reader.readAsDataURL(file);
    });
}

function listDocs() {
    const input = document.getElementById('input-docs');
    
    if (input.files.length > 0) {
        Array.from(input.files).forEach(file => {
            allDocs.push(file);
        });
        input.value = "";
    }
    
    renderDocsList();
}

function renderDocsList() {
    const list = document.getElementById('docs-list');
    const tipoDest = document.getElementById('tipo_destacado');
    const indexDest = document.getElementById('destacado_index');
    list.innerHTML = '';
    
    allDocs.forEach((file, index) => {
        const ext = file.name.split('.').pop().toLowerCase();
        let icon = 'fa-file';
        let color = '#747d8c';
        if(['pdf'].includes(ext)) { icon = 'fa-file-pdf'; color = '#ff4757'; }
        if(['doc','docx'].includes(ext)) { icon = 'fa-file-word'; color = '#2e86de'; }
        if(['xls','xlsx'].includes(ext)) { icon = 'fa-file-excel'; color = '#20bf6b'; }
        if(['zip','rar'].includes(ext)) icon = 'fa-file-archive';
        if(['mp4','mov'].includes(ext)) icon = 'fa-video';

        const div = document.createElement('div');
        div.className = 'doc-card';
        div.innerHTML = `
            <div class="doc-info-wrapper" onclick="setFeatured('doc', ${index})">
                <div class="doc-icon-box" style="background: ${color}">
                    <i class="fas ${icon}"></i>
                </div>
                <div class="doc-details">
                    <span class="doc-filename">${file.name}</span>
                    <span class="doc-meta">
                        ${ext.toUpperCase()} <span class="destacado-tag" style="display: ${tipoDest.value === 'doc' && index == indexDest.value ? 'block' : 'none'}">DESTACADO</span>
                    </span>
                </div>
            </div>
            <div class="doc-actions-btns">
                <div class="action-circle ${tipoDest.value === 'doc' && index == indexDest.value ? 'is-destacado' : ''}" onclick="setFeatured('doc', ${index})">
                    <i class="fas fa-star"></i>
                </div>
                <button type="button" class="action-circle" style="background:#fee2e2; color:#ef4444; border:none;" onclick="removeFile('doc', ${index}, event)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        list.appendChild(div);
    });
}

function setFeatured(type, index) {
    const tipoDest = document.getElementById('tipo_destacado');
    const indexDest = document.getElementById('destacado_index');
    
    tipoDest.value = type;
    indexDest.value = index;
    
    renderImagesPreview();
    renderDocsList();
}

function removeFile(type, index, event) {
    if(event) event.stopPropagation();
    
    const tipoDest = document.getElementById('tipo_destacado');
    const indexDest = document.getElementById('destacado_index');

    if (type === 'image') {
        allImages.splice(index, 1);
        if (tipoDest.value === 'image' && indexDest.value == index) {
            tipoDest.value = 'image'; indexDest.value = 0;
        } else if (tipoDest.value === 'image' && indexDest.value > index) {
            indexDest.value--;
        }
        renderImagesPreview();
    } else {
        allDocs.splice(index, 1);
        if (tipoDest.value === 'doc' && indexDest.value == index) {
            tipoDest.value = 'image'; indexDest.value = 0;
        } else if (tipoDest.value === 'doc' && indexDest.value > index) {
            indexDest.value--;
        }
        renderDocsList();
    }
}

// Sincronizar DataTransfer antes del submit
function syncInputs() {
    const imgInput = document.getElementById('input-imgs');
    const docInput = document.getElementById('input-docs');
    
    const imgDT = new DataTransfer();
    allImages.forEach(file => imgDT.items.add(file));
    imgInput.files = imgDT.files;
    
    const docDT = new DataTransfer();
    allDocs.forEach(file => docDT.items.add(file));
    docInput.files = docDT.files;
}
</script>

<!-- TinyMCE -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.7.0/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: '#tiny-editor',
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    height: 400,
    menubar: false,
    branding: false,
    promotion: false,
    content_style: 'body { font-family:Inter,Helvetica,Arial,sans-serif; font-size:16px }'
});

// Sincronizar TinyMCE y archivos antes de enviar el formulario
document.getElementById('publish-form').onsubmit = function() {
    syncInputs(); // Sincronizar arrays de JS con los inputs de archivo
    tinymce.triggerSave();
    
    // Mostrar loader mientras se suben los archivos
    Swal.fire({
        title: 'Publicando...',
        html: 'Subiendo archivos adjuntos y procesando.<br>Esto puede demorar unos momentos dependiendo del tamaño de los archivos.<br><br><b>Por favor, no cierres esta ventana.</b>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
};
</script>

<style>
    .preview-item { position: relative; }
    .preview-item .remove-btn {
        position: absolute; top: 2px; left: 2px; background: rgba(239, 68, 68, 0.9);
        color: white; border: none; border-radius: 4px; width: 18px; height: 18px;
        font-size: 14px; line-height: 1; cursor: pointer; display: flex; align-items: center;
        justify-content: center; z-index: 10; font-weight: bold;
    }
    .preview-item .remove-btn:hover { background: #ef4444; transform: scale(1.1); }
    .preview-item .click-area {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer; z-index: 5;
    }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
