<?php
$page = 'clientes';
$pageTitle = 'Gestión de Clientes';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/includes/header.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();
$message = '';

// Procesar Formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $nombre = $_POST['nombre'];
        $dominio = $_POST['dominio'];
        $apiKey = bin2hex(random_bytes(16)); // Generar API Key única

        $stmt = $db->prepare("INSERT INTO clientes (nombre, dominio, api_key) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $dominio, $apiKey]);
        $message = "Cliente creado con éxito.";
    } elseif ($action === 'delete') {
        $id = $_POST['id'];
        $stmt = $db->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Cliente eliminado.";
    }
}

$clientes = $db->query("SELECT * FROM clientes ORDER BY id DESC")->fetchAll();
?>

<!-- FontAwesome para iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .input-group {
        margin-bottom: 1.5rem;
    }
    .input-group label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 0.5rem;
    }
    .input-group input {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 0.95rem;
        transition: var(--transition);
        background: #f8f9fa;
    }
    .input-group input:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(0, 98, 255, 0.1);
    }
</style>

<div class="admin-header">
    <h1>Clientes & API Keys</h1>
    <p style="color: var(--text-muted); margin-top: 0.5rem;">Administra los accesos de tus sitios web de forma centralizada.</p>
</div>

<?php if ($message): ?>
<div class="card" style="background: var(--success); color: white; border: none; padding: 1rem; margin-bottom: 2rem;">
    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
    <div>
        <div class="card">
            <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;"><i class="fas fa-plus-circle"></i> Nuevo Cliente</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="input-group">
                    <label>Nombre de la Empresa</label>
                    <input type="text" name="nombre" placeholder="Ej: Mi Portal de Noticias" required>
                </div>
                <div class="input-group">
                    <label>Dominio (opcional)</label>
                    <input type="text" name="dominio" placeholder="ejemplo.com">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-key" style="margin-right: 0.5rem;"></i> Generar API Key
                </button>
            </form>
        </div>
    </div>

    <div>
        <div class="card">
            <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;"><i class="fas fa-list"></i> Clientes Activos</h2>
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>API Key</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
                        <tr><td colspan="3" style="text-align:center; padding: 2rem; color: var(--text-muted);">No hay clientes registrados.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: var(--text-dark);"><?php echo htmlspecialchars($c['nombre']); ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($c['dominio']) ?: 'Sin dominio'; ?></div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem; background: #f0f2f5; padding: 0.4rem 0.8rem; border-radius: 6px; border: 1px solid #ddd;">
                                <code style="font-size: 0.85rem; color: #333;"><?php echo $c['api_key']; ?></code>
                                <i class="far fa-copy" style="cursor: pointer; color: var(--text-muted);" onclick="navigator.clipboard.writeText('<?php echo $c['api_key']; ?>'); alert('Key copiada!');"></i>
                            </div>
                        </td>
                        <td>
                            <form method="POST" onsubmit="return confirm('¿Eliminar este cliente y todas sus publicaciones?');" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                <button type="submit" class="btn" style="background: rgba(255, 23, 68, 0.1); color: var(--danger); padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
