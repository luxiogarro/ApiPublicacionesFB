<?php
$page = 'dashboard';
$pageTitle = 'Dashboard General';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/includes/header.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

// Obtener estadísticas
$totalClientes = $db->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$totalUsuarios = $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$totalPosts = $db->query("SELECT COUNT(*) FROM publicaciones")->fetchColumn();

// Clientes recientes
$recientes = $db->query("SELECT * FROM clientes ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Estadísticas por cliente
$statsClientes = $db->query("
    SELECT c.nombre, COUNT(p.id) as total 
    FROM clientes c 
    LEFT JOIN publicaciones p ON c.id = p.cliente_id 
    GROUP BY c.id, c.nombre
    ORDER BY total DESC
")->fetchAll();
?>

<!-- FontAwesome para iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="admin-header">
    <h1>Centro de Control Global</h1>
    <p style="color: var(--text-muted); margin-top: 0.5rem;">Administración centralizada de publicaciones y clientes de toda la red.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3>Clientes</h3>
                <span class="value"><?php echo $totalClientes; ?></span>
            </div>
            <i class="fas fa-building" style="font-size: 2.5rem; color: rgba(0, 98, 255, 0.1);"></i>
        </div>
    </div>
    <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3>Usuarios</h3>
                <span class="value"><?php echo $totalUsuarios; ?></span>
            </div>
            <i class="fas fa-users" style="font-size: 2.5rem; color: rgba(0, 98, 255, 0.1);"></i>
        </div>
    </div>
    <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3>Publicaciones</h3>
                <span class="value"><?php echo $totalPosts; ?></span>
            </div>
            <i class="fas fa-newspaper" style="font-size: 2.5rem; color: rgba(0, 98, 255, 0.1);"></i>
        </div>
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin:0;"><i class="fas fa-history"></i> Clientes Recientes</h2>
        <a href="clientes.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem;">Gestionar Todos</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Dominio</th>
                <th>API Key</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recientes as $c): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($c['nombre']); ?></strong></td>
                <td><?php echo $c['dominio'] ?: '<span style="color:#ccc;">N/A</span>'; ?></td>
                <td><code style="background: #f0f2f5; padding: 0.2rem 0.5rem; border-radius: 4px;"><?php echo $c['api_key']; ?></code></td>
                <td><?php echo date('d/m/Y', strtotime($c['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin:0;"><i class="fas fa-chart-bar"></i> Publicaciones por Cliente</h2>
        <span class="badge badge-info"><?php echo count($statsClientes); ?> Clientes</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Total Publicaciones</th>
                <th width="15%">Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($statsClientes as $s): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($s['nombre']); ?></strong></td>
                <td>
                    <div style="display:flex; align-items:center; gap: 10px;">
                        <span style="font-size: 1.2rem; font-weight: 700;"><?php echo $s['total']; ?></span>
                        <div style="flex:1; height: 8px; background: #f0f2f5; border-radius: 4px; overflow:hidden;">
                            <div style="width: <?php echo min(100, ($s['total'] / max(1, (int)$totalPosts)) * 100); ?>%; height:100%; background: var(--primary-gradient);"></div>
                        </div>
                    </div>
                </td>
                <td>
                    <?php if ($s['total'] > 0): ?>
                        <span class="badge" style="background:rgba(0, 200, 83, 0.1); color: #00c853;">Activo</span>
                    <?php else: ?>
                        <span class="badge" style="background:#f1f2f6; color: #6c757d;">Sin posts</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
