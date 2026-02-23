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
?>

<!-- FontAwesome para iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="admin-header">
    <h1>Bienvenido, <?php echo $_SESSION['admin_username']; ?></h1>
    <p style="color: var(--text-muted); margin-top: 0.5rem;">Aquí tienes un resumen de tu ecosistema de publicaciones.</p>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
