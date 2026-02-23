<?php
// Prueba de diagnóstico - si ves este texto, el archivo se desplegó correctamente
$page      = 'download_spec';
$pageTitle = 'Hub de Descargas';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/header.php';
?>
<div class="admin-header">
    <h1><i class="fas fa-check-circle" style="color:green;"></i> Hub de Descargas</h1>
    <p style="color:var(--text-muted);margin-top:.5rem">
        El archivo se desplegó correctamente. PHP versión: <?php echo phpversion(); ?>
    </p>
</div>

<div style="background:white;border-radius:16px;border:1px solid #e5e7eb;padding:2rem;box-shadow:0 4px 12px rgba(0,0,0,.06);">
    <h2 style="margin-top:0">&#128294; Información del Servidor</h2>
    <p><strong>BASE_URL:</strong> <?php echo defined('BASE_URL') ? htmlspecialchars(BASE_URL) : 'No definido'; ?></p>
    <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
    <p><strong>Sesión activa:</strong> <?php echo isset($_SESSION['admin_id']) ? 'Sí (ID: '.$_SESSION['admin_id'].')' : 'No'; ?></p>
    <p style="color:green;font-weight:bold;">&#x2705; Si ves esto, el archivo funciona correctamente. El siguiente paso es restaurar todas las funcionalidades.</p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
