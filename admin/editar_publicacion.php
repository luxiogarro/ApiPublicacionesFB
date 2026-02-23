<?php
$page = 'publicaciones';
$pageTitle = 'Editar Publicación';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/autoload.php';
require_once __DIR__ . '/includes/header.php';

use App\Core\Database;
use App\Model\Post;
use App\Model\User;

$db = Database::getInstance()->getConnection();
$message = '';
$error = '';

if (!isset($_GET['id'])) {
    die("ID no proporcionado");
}
$post_id = (int)$_GET['id'];

// Obtener datos del post
$stmt = $db->prepare("SELECT * FROM publicaciones WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    die("Publicación no encontrada");
}

// Obtener adjuntos
$stmtAdj = $db->prepare("SELECT * FROM adjuntos WHERE publicacion_id = ?");
$stmtAdj->execute([$post_id]);
$adjuntos = $stmtAdj->fetchAll();

$existing_images = [];
$existing_docs = [];
$portada_id = null;
$portada_tipo = 'image'; // por defecto

foreach ($adjuntos as $a) {
    $ext = strtolower(pathinfo($a['nombre_original'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $existing_images[] = $a;
        if ($a['es_portada'] == 1) {
            $portada_id = $a['id'];
            $portada_tipo = 'image';
        }
    } else {
        $existing_docs[] = $a;
        if ($a['es_portada'] == 1) {
            $portada_id = $a['id'];
            $portada_tipo = 'doc';
        }
    }
}

// Obtener clientes para el selector
$clientes = $db->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = $_POST['cliente_id'];
    $titulo = $_POST['titulo'] ?? '';
    $contenido = $_POST['contenido'];
    $video_url = $_POST['video_url'] ?? null;
    $usuario_nombre = $_POST['usuario_nombre'] ?: 'Admin Global';
    
    $tipo_destacado = $_POST['tipo_destacado'] ?? 'image'; 
    $destacado_index = isset($_POST['destacado_index']) ? (int)$_POST['destacado_index'] : 0;
    
    // Lógica Fijado
    $fijada = isset($_POST['fijada']) ? 1 : 0;
    $fijada_hasta = !empty($_POST['fijada_hasta']) ? $_POST['fijada_hasta'] : null;
    
    // El destacado_index para nuevos archivos
    // Si queremos setear un archivo EXISTENTE como destacado, pasariamos su ID
    $destacado_existente_id = $_POST['destacado_existente_id'] ?? null;

    if ($cliente_id && $contenido) {
        try {
            // Actualizar Post usando el modelo
            Post::update($post_id, [
                'titulo' => $titulo,
                'contenido' => $contenido,
                'video_url' => $video_url,
                'cliente_id' => $cliente_id,
                'fijada' => $fijada,
                'fijada_hasta' => $fijada_hasta
            ]);

            // Limpiar destacados existentes
            $db->prepare("UPDATE adjuntos SET es_portada = 0 WHERE publicacion_id = ?")->execute([$post_id]);

            // Setear destacado si es un archivo existente
            if ($destacado_existente_id) {
                $db->prepare("UPDATE adjuntos SET es_portada = 1 WHERE id = ?")->execute([$destacado_existente_id]);
            }

            $has_new_multimedia = false;

            // Procesar IMÁGENES nuevas
            if (!empty($_FILES['imagenes']['name'][0])) {
                $imgs = $_FILES['imagenes'];
                for ($i = 0; $i < count($imgs['name']); $i++) {
                    if ($imgs['error'][$i] === UPLOAD_ERR_OK) {
                        $optimizedName = \App\Core\ImageOptimizer::optimize($imgs['tmp_name'][$i], $imgs['name'][$i]);
                        if ($optimizedName) {
                            $es_portada = (!$destacado_existente_id && $tipo_destacado === 'image' && $i === $destacado_index) ? 1 : 0;
                            Post::addAttachment($post_id, [
                                'ruta' => $optimizedName,
                                'tipo' => 'image/webp',
                                'nombre' => $imgs['name'][$i]
                            ], $es_portada);
                            $has_new_multimedia = true;
                        }
                    }
                }
            }

            // Procesar DOCUMENTOS nuevos
            if (!empty($_FILES['documentos']['name'][0])) {
                $docs = $_FILES['documentos'];
                for ($i = 0; $i < count($docs['name']); $i++) {
                    if ($docs['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($docs['name'][$i], PATHINFO_EXTENSION);
                        $filename = uniqid() . '.' . $ext;
                        if (move_uploaded_file($docs['tmp_name'][$i], UPLOAD_DIR . $filename)) {
                            $es_destacado = (!$destacado_existente_id && $tipo_destacado === 'doc' && $i === $destacado_index) ? 1 : 0;
                            Post::addAttachment($post_id, [
                                'ruta' => $filename,
                                'tipo' => $docs['type'][$i],
                                'nombre' => $docs['name'][$i]
                            ], $es_destacado);
                            $has_new_multimedia = true;
                        }
                    }
                }
            }
            
            if ($has_new_multimedia || count($adjuntos) > 0) {
                $db->prepare("UPDATE publicaciones SET tipo = 'multiple' WHERE id = ?")->execute([$post_id]);
            } else {
                 $db->prepare("UPDATE publicaciones SET tipo = 'texto' WHERE id = ?")->execute([$post_id]);
            }

            $message = "¡Publicación actualizada con éxito!";
            
            // Recargar adjuntos actualizados
            $stmtAdj->execute([$post_id]);
            $adjuntos = $stmtAdj->fetchAll();
            $existing_images = [];
            $existing_docs = [];
            foreach ($adjuntos as $a) {
                $ext = strtolower(pathinfo($a['nombre_original'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $existing_images[] = $a;
                    if ($a['es_portada'] == 1) { $portada_id = $a['id']; $portada_tipo = 'image'; }
                } else {
                    $existing_docs[] = $a;
                    if ($a['es_portada'] == 1) { $portada_id = $a['id']; $portada_tipo = 'doc'; }
                }
            }

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
    
    .doc-card {
        background: #fff; border: 1px solid #eee; border-radius: 8px; padding: 10px;
        display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;
    }
    .doc-info-wrapper { display: flex; align-items: center; gap: 10px; cursor: pointer; flex: 1; }
    .doc-icon-box { width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; }
    .doc-details { display: flex; flex-direction: column; }
    .doc-filename { font-size: 0.85rem; font-weight: 600; }
    .doc-meta { font-size: 0.7rem; color: #888; }
    .action-circle { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; background: #f1f2f6; transition: 0.2s; border: none; }
    .action-circle:hover { background: var(--primary); color: white; }
    .action-circle.is-destacado { background: var(--success); color: white; }
</style>

<div class="admin-header" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h1>Editar Publicación</h1>
        <p style="color: var(--text-muted);">Actualiza el contenido o gestiona los archivos adjuntos.</p>
    </div>
    <a href="publicaciones.php" class="btn" style="background:#e0e0e0; color:#333;"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<?php if ($message): ?>
<div class="card" style="background: var(--success); color: white; padding: 1rem; margin-bottom: 2rem;">
    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="card" style="background: var(--danger); color: white; padding: 1rem; margin-bottom: 2rem;">
    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
</div>
<?php endif; ?>

<div class="form-container">
    <form method="POST" enctype="multipart/form-data" id="publish-form">
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="input-group">
                <label>Cliente / Sitio Web</label>
                <select name="cliente_id" required>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo $c['id'] == $post['cliente_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group" style="margin-top:1rem;">
                <label>Autor (Opcional)</label>
                <?php
                $stmtU = $db->prepare("SELECT nombre FROM usuarios WHERE id = ?");
                $stmtU->execute([$post['usuario_id']]);
                $authorName = $stmtU->fetchColumn() ?: 'Admin Global';
                ?>
                <input type="text" name="usuario_nombre" value="<?php echo htmlspecialchars($authorName); ?>">
            </div>
            <div class="input-group" style="margin-top:1rem;">
                <label>Título de la Publicación</label>
                <input type="text" name="titulo" value="<?php echo htmlspecialchars($post['titulo']); ?>" style="font-weight: 700;">
            </div>
            <!-- Video Embebido -->
            <div class="card" style="margin-bottom: 2rem;">
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
                    <input type="text" name="video_url" class="form-control" placeholder="Ej: https://www.youtube.com/watch?v=..." value="<?php echo htmlspecialchars($post['video_url'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Contenido de la Publicación</label>
                <textarea name="contenido" id="tiny-editor"><?php echo htmlspecialchars($post['contenido']); ?></textarea>
            </div>
            <div class="input-group" style="margin-top:1rem; display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #fffcf2; border-radius:8px; border:1px solid #ffeaa7;">
                <label style="margin:0; display:flex; align-items:center; gap: 0.5rem; cursor:pointer;">
                    <input type="checkbox" name="fijada" value="1" id="fijar-checkbox" onchange="toggleFijarDate()" style="width:18px; height:18px;" <?php echo $post['fijada'] ? 'checked' : ''; ?>>
                    <strong><i class="fas fa-thumbtack" style="color:#d35400;"></i> Fijar en la parte superior del Feed</strong>
                </label>
                <div id="fijar-date-container" style="display: <?php echo $post['fijada'] ? 'block' : 'none'; ?>; flex: 1;">
                    <input type="datetime-local" name="fijada_hasta" value="<?php echo $post['fijada_hasta'] ? date('Y-m-d\TH:i', strtotime($post['fijada_hasta'])) : ''; ?>" style="padding: 0.6rem; border-radius:6px; border:1px solid #ddd; width:100%;">
                </div>
            </div>
        </div>

        <div class="upload-sections">
            <!-- Sección Imágenes -->
            <div class="card">
                <h3 style="font-size: 1rem; margin-bottom: 1rem;"><i class="fas fa-images"></i> Imágenes</h3>
                
                <!-- Imágenes Existentes -->
                <?php if (!empty($existing_images)): ?>
                    <h4 style="font-size:0.8rem; color:#888; margin-bottom:10px;">Subidas (<?php echo count($existing_images); ?>)</h4>
                    <div class="preview-grid" style="margin-bottom:1rem;">
                        <?php foreach($existing_images as $img): ?>
                            <div class="preview-item <?php echo $portada_id == $img['id'] ? 'is-cover' : ''; ?>" id="adj-card-<?php echo $img['id']; ?>">
                                <img src="../uploads/<?php echo $img['ruta_archivo']; ?>">
                                <span class="cover-badge">PORTADA</span>
                                <button type="button" class="remove-btn" onclick="eliminarAdjunto(<?php echo $img['id']; ?>)">&times;</button>
                                <div class="click-area" onclick="setFeaturedExistente(<?php echo $img['id']; ?>)"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="upload-box" onclick="document.getElementById('input-imgs').click()">
                    <i class="fas fa-camera"></i>
                    <span>Añadir Fotos</span>
                    <input type="file" name="imagenes[]" id="input-imgs" multiple accept="image/*" style="display:none" onchange="previewImages()">
                </div>
                
                <input type="hidden" name="destacado_existente_id" id="destacado_existente_id" value="<?php echo $portada_id ?: ''; ?>">
                <input type="hidden" name="tipo_destacado" id="tipo_destacado" value="image">
                <input type="hidden" name="destacado_index" id="destacado_index" value="-1">
                
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">Haz clic para <strong>Destacar</strong>. Se guardará al actualizar.</p>
                <div id="imgs-preview" class="preview-grid"></div>
            </div>

            <!-- Sección Archivos -->
            <div class="card">
                <h3 style="font-size: 1rem; margin-bottom: 1rem;"><i class="fas fa-file-export"></i> Documentos</h3>
                
                <!-- Documentos Existentes -->
                <?php if (!empty($existing_docs)): ?>
                    <h4 style="font-size:0.8rem; color:#888; margin-bottom:10px;">Subidos (<?php echo count($existing_docs); ?>)</h4>
                    <div style="margin-bottom:1rem;">
                        <?php foreach($existing_docs as $doc): ?>
                            <?php
                            $ext = strtolower(pathinfo($doc['nombre_original'], PATHINFO_EXTENSION));
                            $icon = 'fa-file'; $color = '#747d8c';
                            if(['pdf']==$ext) { $icon = 'fa-file-pdf'; $color = '#ff4757'; }
                            ?>
                            <div class="doc-card" id="adj-card-<?php echo $doc['id']; ?>">
                                <div class="doc-info-wrapper" onclick="setFeaturedExistente(<?php echo $doc['id']; ?>)">
                                    <div class="doc-icon-box" style="background: <?php echo $color; ?>"><i class="fas <?php echo $icon; ?>"></i></div>
                                    <div class="doc-details">
                                        <span class="doc-filename"><?php echo htmlspecialchars($doc['nombre_original']); ?></span>
                                    </div>
                                </div>
                                <div style="display:flex; gap:5px;">
                                    <div class="action-circle bg-existente-star <?php echo $portada_id == $doc['id'] ? 'is-destacado' : ''; ?>" data-id="<?php echo $doc['id']; ?>" onclick="setFeaturedExistente(<?php echo $doc['id']; ?>)"><i class="fas fa-star"></i></div>
                                    <button type="button" class="action-circle text-danger" onclick="eliminarAdjunto(<?php echo $doc['id']; ?>)"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="upload-box" onclick="document.getElementById('input-docs').click()">
                    <i class="fas fa-file-upload"></i>
                    <span>Añadir Archivos</span>
                    <input type="file" name="documentos[]" id="input-docs" multiple style="display:none" onchange="listDocs()">
                </div>
                <div id="docs-list" class="file-list"></div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
            <i class="fas fa-save"></i> Guardar Cambios
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

function setFeaturedExistente(id) {
    document.getElementById('destacado_existente_id').value = id;
    document.getElementById('destacado_index').value = -1; // Desactivar destacados nuevos
    
    // UI Actualización manual para no recargar (clases)
    document.querySelectorAll('.preview-item').forEach(el => el.classList.remove('is-cover'));
    document.querySelectorAll('.bg-existente-star').forEach(el => el.classList.remove('is-destacado'));
    
    let imgCard = document.getElementById('adj-card-' + id);
    if(imgCard && imgCard.classList.contains('preview-item')) {
        imgCard.classList.add('is-cover');
    }
    
    let docStar = document.querySelector('.bg-existente-star[data-id="'+id+'"]');
    if(docStar) {
        docStar.classList.add('is-destacado');
    }
    
    renderImagesPreview(); // Renderiza los *nuevos* sin destacar
    renderDocsList(); // Renderiza docs *nuevos* sin destacar
}

function eliminarAdjunto(id) {
    Swal.fire({
        title: '¿Eliminar archivo?',
        text: "Se borrará permanentemente de inmediato.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, borrar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('delete_adjunto.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            }).then(r => r.json()).then(data => {
                if(data.success) {
                    document.getElementById('adj-card-' + id).remove();
                    if(document.getElementById('destacado_existente_id').value == id) {
                        document.getElementById('destacado_existente_id').value = '';
                    }
                    Swal.fire('¡Eliminado!', 'Archivo removido.', 'success');
                }
            });
        }
    });
}


// --- Lógica de archivos NUEVOS (Secuenciales) ---

function previewImages() {
    const input = document.getElementById('input-imgs');
    if (input.files.length > 0) {
        Array.from(input.files).forEach(file => allImages.push(file));
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
                <span class="cover-badge">NUEVO PORTADA</span>
                <button type="button" class="remove-btn" onclick="removeFile('image', ${index}, event)">&times;</button>
                <div class="click-area" onclick="setFeaturedNuevo('image', ${index})"></div>
            `;
            preview.appendChild(div);
        }
        reader.readAsDataURL(file);
    });
}

function listDocs() {
    const input = document.getElementById('input-docs');
    if (input.files.length > 0) {
        Array.from(input.files).forEach(file => allDocs.push(file));
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
        let icon = 'fa-file'; let color = '#747d8c';
        if(['pdf'].includes(ext)) { icon = 'fa-file-pdf'; color = '#ff4757'; }

        const div = document.createElement('div');
        div.className = 'doc-card';
        div.innerHTML = `
            <div class="doc-info-wrapper" onclick="setFeaturedNuevo('doc', ${index})">
                <div class="doc-icon-box" style="background: ${color}"><i class="fas ${icon}"></i></div>
                <div class="doc-details">
                    <span class="doc-filename">${file.name}</span> <span style="font-size:10px;color:blue">(Nuevo)</span>
                </div>
            </div>
            <div class="doc-actions-btns">
                <div class="action-circle ${tipoDest.value === 'doc' && index == indexDest.value ? 'is-destacado' : ''}" onclick="setFeaturedNuevo('doc', ${index})">
                    <i class="fas fa-star"></i>
                </div>
                <button type="button" class="action-circle text-danger" onclick="removeFile('doc', ${index}, event)"><i class="fas fa-trash"></i></button>
            </div>
        `;
        list.appendChild(div);
    });
}

function setFeaturedNuevo(type, index) {
    document.getElementById('destacado_existente_id').value = ''; // Desactivar destacados existentes
    document.getElementById('tipo_destacado').value = type;
    document.getElementById('destacado_index').value = index;
    
    // Limpiar UI de existentes
    document.querySelectorAll('.preview-item').forEach(el => el.classList.remove('is-cover'));
    document.querySelectorAll('.bg-existente-star').forEach(el => el.classList.remove('is-destacado'));
    
    renderImagesPreview();
    renderDocsList();
}

function removeFile(type, index, event) {
    if(event) event.stopPropagation();
    const tipoDest = document.getElementById('tipo_destacado');
    const indexDest = document.getElementById('destacado_index');

    if (type === 'image') {
        allImages.splice(index, 1);
        if (tipoDest.value === 'image' && indexDest.value == index) { indexDest.value = -1; }
        else if (tipoDest.value === 'image' && indexDest.value > index) { indexDest.value--; }
        renderImagesPreview();
    } else {
        allDocs.splice(index, 1);
        if (tipoDest.value === 'doc' && indexDest.value == index) { indexDest.value = -1; }
        else if (tipoDest.value === 'doc' && indexDest.value > index) { indexDest.value--; }
        renderDocsList();
    }
}

function syncInputs() {
    const imgDT = new DataTransfer();
    allImages.forEach(file => imgDT.items.add(file));
    document.getElementById('input-imgs').files = imgDT.files;
    
    const docDT = new DataTransfer();
    allDocs.forEach(file => docDT.items.add(file));
    document.getElementById('input-docs').files = docDT.files;
}
</script>

<!-- TinyMCE -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.7.0/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: '#tiny-editor',
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline | link image media table | align lineheight | numlist bullist | emoticons',
    language: 'es', height: 400, menubar: false, branding: false, promotion: false,
    content_style: 'body { font-family:Inter,Helvetica,Arial,sans-serif; font-size:16px }'
});

document.getElementById('publish-form').onsubmit = function() {
    syncInputs();
    tinymce.triggerSave();
    
    // Mostrar loader mientras se suben los archivos
    Swal.fire({
        title: 'Guardando publicación...',
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
