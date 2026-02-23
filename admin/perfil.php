<?php
$page = 'perfil';
$pageTitle = 'Mi Perfil';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/includes/header.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($current_password && $new_password && $confirm_password) {
        if ($new_password !== $confirm_password) {
            $error = 'Las nuevas contraseñas no coinciden.';
        } else {
            // Obtener el admin actual
            $stmt = $db->prepare("SELECT password FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($current_password, $admin['password'])) {
                // Actualizar contraseña
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $stmtUpd = $db->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $stmtUpd->execute([$hashed_password, $_SESSION['admin_id']]);
                $message = 'Contraseña actualizada con éxito.';
            } else {
                $error = 'La contraseña actual es incorrecta.';
            }
        }
    } else {
        $error = 'Por favor, complete todos los campos.';
    }
}
?>

<div class="admin-header">
    <h1>Configuración de Perfil</h1>
    <p style="color: var(--text-muted); margin-top: 0.5rem;">Administra tus credenciales de acceso al panel.</p>
</div>

<div class="card" style="max-width: 500px; margin: 0 auto;">
    <h2 style="margin-top: 0; margin-bottom: 1.5rem;"><i class="fas fa-key" style="color: var(--primary);"></i> Cambiar Contraseña</h2>
    
    <?php if ($message): ?>
        <div style="background: #e8f5e9; color: #2e7d32; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 600;">
            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div style="background: #ffebee; color: #c62828; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 600;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;">Contraseña Actual</label>
            <input type="password" name="current_password" required placeholder="••••••••" style="width: 100%; padding: 0.8rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;">
        </div>
        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;">Nueva Contraseña</label>
            <input type="password" name="new_password" required placeholder="••••••••" style="width: 100%; padding: 0.8rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;">
        </div>
        <div style="margin-bottom: 2rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;">Confirmar Nueva Contraseña</label>
            <input type="password" name="confirm_password" required placeholder="••••••••" style="width: 100%; padding: 0.8rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;">
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
            Actualizar Credenciales
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
