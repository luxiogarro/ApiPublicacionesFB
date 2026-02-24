<?php
$page = 'gestion_publicaciones';
$pageTitle = 'Gestión de Publicaciones';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/includes/header.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

require_once __DIR__ . '/../src/Model/Post.php';
use App\Model\Post;

$db = Database::getInstance()->getConnection();

// Filtros
$filters = [
    'search' => $_GET['search'] ?? null,
    'cliente_id' => $_GET['cliente_id'] ?? null,
    'date_from' => $_GET['date_from'] ?? null,
    'date_to' => $_GET['date_to'] ?? null
];

// Paginación
$limit = 10;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($page_num < 1) $page_num = 1;
$offset = ($page_num - 1) * $limit;

// Obtener total de registros para el conteo de páginas (respetando filtros)
$where = "1=1";
$params = [];

if (!empty($filters['cliente_id'])) {
    $where .= " AND cliente_id = ?";
    $params[] = $filters['cliente_id'];
}
if (!empty($filters['search'])) {
    $where .= " AND (titulo LIKE ? OR contenido LIKE ?)";
    $params[] = '%' . $filters['search'] . '%';
    $params[] = '%' . $filters['search'] . '%';
}
if (!empty($filters['date_from'])) {
    $where .= " AND created_at >= ?";
    $params[] = $filters['date_from'] . ' 00:00:00';
}
if (!empty($filters['date_to'])) {
    $where .= " AND created_at <= ?";
    $params[] = $filters['date_to'] . ' 23:59:59';
}

$stmtCount = $db->prepare("SELECT COUNT(*) FROM publicaciones WHERE $where");
$stmtCount->execute($params);
$total_posts = $stmtCount->fetchColumn();
$total_pages = ceil($total_posts / $limit);

$posts = Post::getAllGlobal($limit, $offset, $filters);

$clientes = $db->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC")->fetchAll();
?>

<style>
    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    .table-container {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        border: 1px solid var(--border);
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 1rem 1.5rem;
        text-align: left;
        border-bottom: 1px solid var(--border);
    }
    th {
        background-color: #f8f9fa;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    td {
        font-size: 0.9rem;
        color: var(--text-dark);
        vertical-align: middle;
    }
    tr:last-child td {
        border-bottom: none;
    }
    tr:hover {
        background-color: #f8f9fa;
    }
    
    .actions-cell {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    .btn-action {
        width: 35px;
        height: 35px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: var(--transition);
        color: white;
        text-decoration: none;
    }
    
    .btn-edit { background: var(--primary); }
    .btn-edit:hover { background: #004ecc; transform: translateY(-2px); }
    
    .btn-delete { background: var(--danger); }
    .btn-delete:hover { background: #d50000; transform: translateY(-2px); }
    
    .content-snippet {
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        color: var(--text-muted);
    }
    .pinned-row {
        background: #fff9c4 !important;
        border-left: 5px solid #f9a825;
    }
    .pinned-row:hover {
        background: #fff59d !important;
    }
    .badge-pin {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: linear-gradient(135deg, #ffd32a, #f9a825);
        color: #5c3900;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 800;
        margin-left: 6px;
        vertical-align: middle;
        box-shadow: 0 2px 6px rgba(249, 168, 37, 0.5);
    }
    .pin-until-text {
        display: block;
        font-size: 0.73rem;
        color: #b7791f;
        margin-top: 3px;
        font-weight: 500;
    }
    .pagination {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 2rem;
        padding-bottom: 2rem;
    }
    .pagination a, .pagination span {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        background: white;
        border: 1px solid var(--border);
        color: var(--text-dark);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
    }
    .pagination a:hover {
        border-color: var(--primary);
        color: var(--primary);
    }
    .pagination .active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }
    .pagination .disabled {
        color: #ccc;
        pointer-events: none;
    }

    /* Estilos Filtros */
    .filters-bar {
        background: white;
        padding: 1.25rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        margin-bottom: 1.5rem;
    }
    .filters-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 0.75rem;
        align-items: end;
    }
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }
    .filter-group label {
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
    }
    .filter-group input, .filter-group select {
        padding: 0.5rem;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 0.85rem;
    }
    .btn-filter {
        background: var(--primary);
        color: white;
        border: none;
        padding: 0.5rem;
        border-radius: 6px;
        font-weight: 700;
        cursor: pointer;
    }
    .btn-clear {
        background: #f1f2f6;
        color: var(--text-dark);
        border: none;
        padding: 0.5rem;
        border-radius: 6px;
        font-weight: 700;
        text-decoration: none;
        text-align: center;
        font-size: 0.85rem;
    }
</style>

<div class="admin-header">
    <h1>Gestión Centralizada</h1>
    <a href="nueva_publicacion.php" class="btn"><i class="fas fa-plus"></i> Nueva Publicación Global</a>
</div>

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
        <button type="submit" class="btn-filter"><i class="fas fa-search"></i> Filtrar</button>
        <a href="gestion_publicaciones.php" class="btn-clear"><i class="fas fa-times"></i> Limpiar</a>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th width="5%">ID</th>
                <th width="30%">Título / Contenido</th>
                <th width="15%">Cliente</th>
                <th width="15%">Autor</th>
                <th width="15%">Fecha</th>
                <th width="10%">Tipo</th>
                <th width="10%">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($posts)): ?>
            <tr>
                <td colspan="7" style="text-align: center; padding: 2rem;">No hay publicaciones registradas.</td>
            </tr>
            <?php else: ?>
                <?php foreach ($posts as $p): ?>
                <?php
                $is_pinned = !empty($p['es_fijada_activa']);
                $pin_tooltip = '';
                if ($is_pinned && !empty($p['fijada_hasta'])) {
                    $pin_tooltip = 'Fijada hasta: ' . date('d/m/Y H:i', strtotime($p['fijada_hasta']));
                } elseif ($is_pinned) {
                    $pin_tooltip = 'Fijada permanentemente';
                }
                ?>
                <tr id="row-<?php echo $p['id']; ?>" class="<?php echo $is_pinned ? 'pinned-row' : ''; ?>">
                    <td style="font-weight: 600; color: var(--primary);">#<?php echo $p['id']; ?></td>
                    <td>
                        <div style="font-weight: 700; margin-bottom: 4px;">
                            <?php echo !empty($p['titulo']) ? htmlspecialchars($p['titulo']) : '<i style="color:#aaa;">Sin título</i>'; ?>
                            <?php if ($is_pinned): ?>
                                <span class="badge-pin" title="<?php echo htmlspecialchars($pin_tooltip); ?>">
                                    <i class="fas fa-thumbtack"></i> FIJADO
                                </span>
                                <?php if (!empty($p['fijada_hasta'])): ?>
                                    <span class="pin-until-text"><i class="far fa-clock"></i> <?php echo htmlspecialchars($pin_tooltip); ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <span class="content-snippet"><?php echo strip_tags($p['contenido']); ?></span>
                    </td>
                    <td><span class="badge badge-info"><?php echo htmlspecialchars($p['cliente_nombre']); ?></span></td>
                    <td><?php echo htmlspecialchars($p['usuario_nombre']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($p['created_at'])); ?></td>
                    <td>
                        <?php if ($p['tipo'] == 'multiple'): ?>
                            <span class="badge" style="background:var(--warning); color:#333;"><i class="fas fa-layer-group"></i> Media</span>
                        <?php elseif (!empty($p['video_url'])): ?>
                            <span class="badge" style="background:#ff0000; color:white;"><i class="fab fa-youtube"></i> Video</span>
                        <?php else: ?>
                            <span class="badge" style="background:#e0e0e0; color:#333;"><i class="fas fa-align-left"></i> Texto</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions-cell">
                        <a href="editar_publicacion.php?id=<?php echo $p['id']; ?>" class="btn-action btn-edit" title="Editar">
                            <i class="fas fa-pen"></i>
                        </a>
                        <button type="button" class="btn-action btn-delete" title="Eliminar" onclick="confirmarEliminar(<?php echo $p['id']; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Controles de Paginación -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php
            // Función auxiliar para mantener parámetros de filtros en los links de paginación
            function getPaginationUrl($p, $filters) {
                $params = ['p' => $p];
                if (!empty($filters['search'])) $params['search'] = $filters['search'];
                if (!empty($filters['cliente_id'])) $params['cliente_id'] = $filters['cliente_id'];
                if (!empty($filters['date_from'])) $params['date_from'] = $filters['date_from'];
                if (!empty($filters['date_to'])) $params['date_to'] = $filters['date_to'];
                return '?' . http_build_query($params);
            }
            ?>
            <a href="<?php echo getPaginationUrl($page_num - 1, $filters); ?>" class="<?php echo ($page_num <= 1) ? 'disabled' : ''; ?>">
                <i class="fas fa-chevron-left"></i>
            </a>

            <?php
            $start = max(1, $page_num - 2);
            $end = min($total_pages, $page_num + 2);

            if ($start > 1) {
                echo '<a href="' . getPaginationUrl(1, $filters) . '">1</a>';
                if ($start > 2) echo '<span>...</span>';
            }

            for ($i = $start; $i <= $end; $i++) {
                $active = ($i == $page_num) ? 'active' : '';
                $url = getPaginationUrl($i, $filters);
                echo "<a href='$url' class='$active'>$i</a>";
            }

            if ($end < $total_pages) {
                if ($end < $total_pages - 1) echo '<span>...</span>';
                echo '<a href="' . getPaginationUrl($total_pages, $filters) . '">' . $total_pages . '</a>';
            }
            ?>

            <a href="<?php echo getPaginationUrl($page_num + 1, $filters); ?>" class="<?php echo ($page_num >= $total_pages) ? 'disabled' : ''; ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmarEliminar(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto! Se borrarán los archivos adjuntos y el registro de la BD de forma inmediata.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff1744',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('delete_publicacion.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire('¡Eliminado!', 'El registro publico ha sido borrado.', 'success');
                    const row = document.getElementById('row-' + id);
                    row.style.transition = 'opacity 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                } else {
                    Swal.fire('Error', data.message || 'Hubo un problema al eliminar.', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Ocurrió un error en la red.', 'error');
            });
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
