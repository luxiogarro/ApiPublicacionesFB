<?php
$page = 'publicaciones';
$pageTitle = 'Monitor de Publicaciones';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/includes/header.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

require_once __DIR__ . '/../src/Model/Post.php';
require_once __DIR__ . '/includes/utils.php';

use App\Model\Post;

$db = Database::getInstance()->getConnection();

// Filtros
$filters = [
    'search' => $_GET['search'] ?? null,
    'cliente_id' => $_GET['cliente_id'] ?? null,
    'date_from' => $_GET['date_from'] ?? null,
    'date_to' => $_GET['date_to'] ?? null
];

$limit = 5;
$offset = 0;
$posts = Post::getAllGlobal($limit, $offset, $filters);

$clientes = $db->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC")->fetchAll();
?>

<!-- FontAwesome para iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Fancybox para galería de imágenes -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css">

<style>
    .feed-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .post-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
        border: 1px solid rgba(0,0,0,0.05);
        overflow: hidden;
        transition: transform 0.2s ease;
    }
    .post-card:hover {
        transform: translateY(-2px);
    }
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
    .pinned-badge .pin-until {
        font-weight: 500;
        font-size: 0.75rem;
        opacity: 0.8;
        margin-left: auto;
    }
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
    .avatar-placeholder {
        width: 45px;
        height: 45px;
        background: var(--primary-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1.2rem;
        box-shadow: 0 4px 10px rgba(0, 98, 255, 0.2);
    }
    .author-name {
        font-weight: 700;
        font-size: 1rem;
        color: var(--text-dark);
        margin: 0;
    }
    .post-meta {
        font-size: 0.8rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .client-tag {
        background: #e7f3ff;
        color: var(--primary);
        padding: 2px 8px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.7rem;
    }
    .post-content {
        padding: 0 1.25rem 1.25rem;
        font-size: 1.05rem;
        line-height: 1.6;
        color: #333;
    }
    .adjuntos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 8px;
        padding: 0 1.25rem 1.25rem;
    }
    .adjunto-item {
        background: #f8f9fa;
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.5rem;
        transition: var(--transition);
        text-decoration: none;
        color: var(--text-dark);
    }
    .adjunto-item:hover {
        background: #fff;
        border-color: var(--primary);
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .file-icon {
        font-size: 2rem;
        color: var(--primary);
    }
    .file-name {
        font-size: 0.75rem;
        font-weight: 600;
        word-break: break-all;
    }

    /* Video Embed responsive */
    .video-container {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 */
        height: 0;
        overflow: hidden;
        border-radius: 12px;
        margin: 0 1.25rem 1.25rem;
        background: #000;
        box-shadow: var(--shadow);
    }
    .video-container iframe,
    .video-container object,
    .video-container embed {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }
    /* Estilos Filtros */
    .filters-bar {
        background: white;
        padding: 1.5rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        margin-bottom: 2rem;
    }
    .filters-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        align-items: end;
    }
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }
    .filter-group label {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
    }
    .filter-group input, .filter-group select {
        padding: 0.6rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 0.9rem;
    }
    .btn-filter {
        background: var(--primary);
        color: white;
        border: none;
        padding: 0.6rem;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
    }
    .btn-clear {
        background: #f1f2f6;
        color: var(--text-dark);
        border: none;
        padding: 0.6rem;
        border-radius: 8px;
        font-weight: 700;
        text-decoration: none;
        text-align: center;
        font-size: 0.9rem;
    }
</style>

<?php
// Ya no necesitamos definir getEmbedUrl aquí, viene de includes/utils.php
?>

<div class="admin-header">
    <h1>Monitor Global (Feed)</h1>
    <p style="color: var(--text-muted); margin-top: 0.5rem;">Supervisión centralizada de contenido de toda la red de clientes.</p>
</div>

<div class="feed-container">
    <!-- Barra de Filtros -->
    <div class="filters-bar">
        <form action="" method="GET" class="filters-form">
            <div class="filter-group">
                <label>Buscar</label>
                <input type="text" name="search" placeholder="Título o contenido..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
            </div>
            <div class="filter-group">
                <label>Cliente</label>
                <select name="cliente_id">
                    <option value="">Todos los Clientes</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo ($filters['cliente_id'] == $c['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Desde</label>
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>">
            </div>
            <div class="filter-group">
                <label>Hasta</label>
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>">
            </div>
            <button type="submit" class="btn-filter"><i class="fas fa-search"></i></button>
            <a href="publicaciones.php" class="btn-clear"><i class="fas fa-times"></i></a>
        </form>
    </div>
    <?php if (empty($posts)): ?>
        <div class="card" style="text-align:center;">
            <i class="fas fa-ghost" style="font-size: 3rem; color: #ddd; margin-bottom: 1rem;"></i>
            <h3>No hay publicaciones aún</h3>
            <p>Las publicaciones de tus clientes aparecerán aquí.</p>
        </div>
    <?php endif; ?>

    <div id="feed-items">
        <?php foreach ($posts as $p): ?>
            <?php include __DIR__ . '/includes/post_card.php'; ?>
        <?php endforeach; ?>
    </div>

    <!-- Indicador de carga para Infinite Scroll -->
    <div id="scroll-sentinel" style="height: 50px; display: flex; align-items: center; justify-content: center; margin-top: 2rem;">
        <div class="loader-spinner" id="loader-spinner" style="display:none;">
            <i class="fas fa-spinner fa-spin" style="font-size: 1.5rem; color: var(--primary);"></i>
        </div>
    </div>
</div>

<script>
    let offset = <?php echo $limit; ?>;
    const limit = <?php echo $limit; ?>;
    let loading = false;
    let allLoaded = false;

    // Filtros activos para el fetch
    const activeFilters = {
        search: '<?php echo $filters['search'] ?? ''; ?>',
        cliente_id: '<?php echo $filters['cliente_id'] ?? ''; ?>',
        date_from: '<?php echo $filters['date_from'] ?? ''; ?>',
        date_to: '<?php echo $filters['date_to'] ?? ''; ?>'
    };

    const sentinel = document.getElementById('scroll-sentinel');
    const feedContainer = document.getElementById('feed-items');
    const spinner = document.getElementById('loader-spinner');

    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !loading && !allLoaded) {
            loadMorePosts();
        }
    }, { threshold: 0.1 });

    observer.observe(sentinel);

    async function loadMorePosts() {
        loading = true;
        spinner.style.display = 'block';

        try {
            // Construir query string con filtros
            let url = `ajax_load_posts.php?limit=${limit}&offset=${offset}`;
            if (activeFilters.search) url += `&search=${encodeURIComponent(activeFilters.search)}`;
            if (activeFilters.cliente_id) url += `&cliente_id=${activeFilters.cliente_id}`;
            if (activeFilters.date_from) url += `&date_from=${activeFilters.date_from}`;
            if (activeFilters.date_to) url += `&date_to=${activeFilters.date_to}`;

            const response = await fetch(url);
            const html = await response.text();

            if (html.trim() === '') {
                allLoaded = true;
                sentinel.innerHTML = '<p style="color:var(--text-muted); font-size:0.9rem;">No hay más publicaciones para mostrar.</p>';
            } else {
                feedContainer.insertAdjacentHTML('beforeend', html);
                offset += limit;
                // Re-inicializar Fancybox si es necesario (generalmente lo hace solo si es dinámico)
                if (typeof Fancybox !== 'undefined') {
                    Fancybox.bind("[data-fancybox]");
                }
            }
        } catch (error) {
            console.error('Error al cargar más publicaciones:', error);
        } finally {
            loading = false;
            spinner.style.display = 'none';
        }
    }
</script>
</div>

<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<script>
Fancybox.bind("[data-fancybox]", {
    // Personalización similar a FB
    Images: {
        zoom: false
    },
    Toolbar: {
        display: {
            left: [],
            middle: [],
            right: ["close"],
        },
    }
});

function toggleContent(id) {
    const text = document.getElementById('content-' + id);
    const btn = document.getElementById('btn-' + id);
    if (text.classList.contains('expanded')) {
        text.classList.remove('expanded');
        btn.innerText = 'Ver más...';
    } else {
        text.classList.add('expanded');
        btn.innerText = 'Ver menos';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.post-content-text').forEach(el => {
        const id = el.id.replace('content-', '');
        const btn = document.getElementById('btn-' + id);
        // Verificar si el contenido excede el límite visible (4 líneas aprox)
        if (el.scrollHeight > el.offsetHeight + 5) { // +5 margen de error
            btn.style.display = 'inline-block';
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
