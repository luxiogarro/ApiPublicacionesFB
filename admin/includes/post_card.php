<?php
/**
 * admin/includes/post_card.php
 * Renderiza una publicación individual. 
 * Requiere que la variable $p esté definida con los datos de la publicación y sus adjuntos.
 */
if (!isset($p)) return;

$is_pinned = !empty($p['es_fijada_activa']);

// Procesar adjuntos (que ya vienen en $p['adjuntos'] gracias al modelo Post)
$adjuntos = $p['adjuntos'] ?? [];
$imagenes = [];
$documentos = [];
$portada = null;

foreach ($adjuntos as $a) {
    $ext = strtolower(pathinfo($a['nombre_original'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $imagenes[] = $a;
        if ($a['es_portada'] == 1) {
            $portada = $a;
        }
    } else {
        $documentos[] = $a;
        if ($a['es_portada'] == 1) {
            $portada = $a;
        }
    }
}

// Si no hay portada marcada, usamos la primera imagen
if (!$portada && !empty($imagenes)) {
    $portada = $imagenes[0];
}

// Ordenar imágenes para miniaturas
$miniaturas = [];
foreach ($imagenes as $img) {
    if ($portada && $img['id'] === $portada['id']) continue;
    $miniaturas[] = $img;
}

$portada_img = null;
$portada_doc = null;

if ($portada) {
    $extPort = strtolower(pathinfo($portada['nombre_original'], PATHINFO_EXTENSION));
    if (in_array($extPort, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $portada_img = $portada;
    } else {
        $portada_doc = $portada;
    }
}
?>

<div class="post-card <?php echo $is_pinned ? 'is-pinned' : ''; ?>" data-id="<?php echo $p['id']; ?>">
    <?php if ($is_pinned): ?>
        <div class="pinned-badge">
            <i class="fas fa-thumbtack"></i> Publicación Fijada
            <?php if (!empty($p['fijada_hasta'])): ?>
                <span class="pin-until"><i class="far fa-clock"></i> hasta <?php echo date('d/m/Y H:i', strtotime($p['fijada_hasta'])); ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="post-header">
        <div class="user-info">
            <?php if (!empty($p['usuario_avatar'])): ?>
                <img src="<?php echo $p['usuario_avatar']; ?>" style="width:45px; height:45px; border-radius:50%; object-fit:cover; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <?php else: ?>
                <div class="avatar-placeholder">
                    <?php echo substr($p['usuario_nombre'], 0, 1); ?>
                </div>
            <?php endif; ?>
            <div>
                <h4 class="author-name"><?php echo htmlspecialchars($p['usuario_nombre']); ?></h4>
                <div class="post-meta">
                    <?php if (!empty($p['cliente_nombre'])): ?>
                        <span class="client-tag"><i class="fas fa-building"></i> <?php echo htmlspecialchars($p['cliente_nombre']); ?></span> •
                    <?php endif; ?>
                    <span><i class="far fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($p['created_at'])); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="post-content-container">
        <?php if (!empty($p['titulo'])): ?>
            <h3 style="margin-bottom: 0.5rem; font-weight: 800; font-size: 1.15rem; color: var(--text-dark);">
                <?php echo htmlspecialchars($p['titulo']); ?>
            </h3>
        <?php endif; ?>
        <div class="post-content-text" id="content-<?php echo $p['id']; ?>">
            <?php echo $p['contenido']; ?>
        </div>
        <button class="read-more-btn" id="btn-<?php echo $p['id']; ?>" onclick="toggleContent(<?php echo $p['id']; ?>)">Ver más...</button>
    </div>

    <!-- Video Embebido -->
    <?php if (!empty($p['video_url'])): ?>
        <?php $embed = getEmbedUrl($p['video_url']); ?>
        <?php if ($embed): ?>
            <div class="video-container">
                <?php echo $embed; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Cuadrícula de Imágenes -->
    <?php if ($portada_img): ?>
        <div class="social-grid">
            <a href="../uploads/<?php echo $portada_img['ruta_archivo']; ?>" data-fancybox="gallery-<?php echo $p['id']; ?>" class="featured-link">
                <img src="../uploads/<?php echo $portada_img['ruta_archivo']; ?>" class="featured-image" alt="Portada">
            </a>
            
            <?php if (!empty($miniaturas)): ?>
                <div class="thumbnails-row">
                    <?php foreach ($miniaturas as $index => $m): ?>
                        <?php if ($index < 3): ?>
                            <a href="../uploads/<?php echo $m['ruta_archivo']; ?>" data-fancybox="gallery-<?php echo $p['id']; ?>" class="thumbnail-wrapper">
                                <img src="../uploads/<?php echo $m['ruta_archivo']; ?>" class="thumbnail-item" alt="Miniatura">
                            </a>
                        <?php elseif ($index === 3): ?>
                            <a href="../uploads/<?php echo $m['ruta_archivo']; ?>" data-fancybox="gallery-<?php echo $p['id']; ?>" class="thumbnail-wrapper">
                                <img src="../uploads/<?php echo $m['ruta_archivo']; ?>" class="thumbnail-item" alt="Miniatura">
                                <?php if (count($miniaturas) > 4): ?>
                                    <div class="more-images-overlay">
                                        +<?php echo (count($miniaturas) - 3); ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                        <?php else: ?>
                            <a href="../uploads/<?php echo $m['ruta_archivo']; ?>" data-fancybox="gallery-<?php echo $p['id']; ?>" style="display:none;"></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Documento Destacado -->
    <?php if ($portada_doc): ?>
        <?php 
        $extPort = strtolower(pathinfo($portada_doc['nombre_original'], PATHINFO_EXTENSION));
        if ($extPort === 'pdf'): 
        ?>
            <div class="pdf-viewer-container">
                <div class="pdf-header">
                    <div class="pdf-title">
                        <i class="fas fa-file-pdf"></i>
                        <span><?php echo htmlspecialchars($portada_doc['nombre_original']); ?></span>
                    </div>
                </div>
                <embed src="../uploads/<?php echo $portada_doc['ruta_archivo']; ?>" type="application/pdf" class="pdf-embed">
            </div>
        <?php else: ?>
            <div class="pdf-viewer-container" style="background: white; border: 1px solid #ddd; border-radius:12px; margin: 0 1.25rem 1.25rem;">
                <!-- UI simplificada para el fragmento -->
                <div style="padding: 2rem; text-align: center;">
                    <i class="fas fa-file-alt" style="font-size: 3rem; color: #747d8c; margin-bottom: 1rem;"></i>
                    <h4><?php echo htmlspecialchars($portada_doc['nombre_original']); ?></h4>
                    <a href="../uploads/<?php echo $portada_doc['ruta_archivo']; ?>" target="_blank" class="btn-download-simple">Descargar</a>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Documentos Adicionales -->
    <?php if (!empty($documentos)): ?>
        <div class="additional-docs-section">
            <span class="section-title">Documentos Adicionales</span>
            <div class="docs-column">
                <?php foreach ($documentos as $d): ?>
                    <?php 
                    $ext = strtolower(pathinfo($d['nombre_original'], PATHINFO_EXTENSION));
                    $is_destacado = ($portada_doc && $d['id'] === $portada_doc['id']);
                    ?>
                    <div class="doc-card">
                        <span class="doc-filename"><?php echo htmlspecialchars($d['nombre_original']); ?></span>
                        <a href="../uploads/<?php echo $d['ruta_archivo']; ?>" target="_blank" class="download-link-icon"><i class="fas fa-download"></i></a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
