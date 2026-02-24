<?php
// admin/includes/header.php
require_once __DIR__ . '/auth.php';
checkAuth();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - Admin API</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0062ff;
            --primary-gradient: linear-gradient(135deg, #0062ff 0%, #00d2ff 100%);
            --secondary: #6c757d;
            --success: #00c853;
            --danger: #ff1744;
            --warning: #ffea00;
            --sidebar-bg: #f8f9fa;
            --main-bg: #f0f2f5;
            --card-bg: rgba(255, 255, 255, 0.95);
            --border: #e0e0e0;
            --text-dark: #212529;
            --text-muted: #6c757d;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            --radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--main-bg);
            margin: 0;
            display: flex;
            min-height: 100vh;
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            transition: var(--transition);
        }
        
        .sidebar-header {
            padding: 2rem 1.5rem;
            text-align: left;
        }
        
        .sidebar-header h2 {
            margin: 0;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        
        .nav-links {
            flex: 1;
            padding: 0 1rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.25rem;
            color: var(--secondary);
            text-decoration: none;
            border-radius: var(--radius);
            margin-bottom: 0.5rem;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .nav-link:hover {
            background: rgba(0, 98, 255, 0.05);
            color: var(--primary);
            transform: translateX(5px);
        }
        
        .nav-link.active {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 98, 255, 0.3);
        }
        
        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--border);
            background: rgba(0,0,0,0.02);
        }
        
        .logout-btn {
            color: var(--danger);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 2.5rem;
            width: calc(100% - 280px);
        }
        
        .admin-header {
            margin-bottom: 2.5rem;
            animation: fadeInDown 0.5s ease-out;
        }
        
        h1 {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -1px;
            margin: 0;
        }
        
        /* Cards & Elements */
        .card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            transition: var(--transition);
        }
        
        .card:hover {
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary);
            transition: var(--transition);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            margin: 0;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            font-weight: 700;
        }
        
        .stat-card .value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-top: 0.5rem;
            display: block;
        }

        /* Tables & UI Components */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        th {
            background: transparent;
            color: var(--text-muted);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 1rem;
            border-bottom: 2px solid var(--border);
        }
        
        td {
            padding: 1.2rem 1rem;
            border-bottom: 1px solid var(--border);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            border: none;
            transition: var(--transition);
            text-decoration: none;
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 98, 255, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 98, 255, 0.4);
        }
        
        .badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        
        .badge-info {
            background: rgba(0, 98, 255, 0.1);
            color: var(--primary);
        }

        /* Animations */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Social Media Grid Styles */
        .social-grid {
            margin: 0.5rem 1.25rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 4px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #eee;
        }
        
        .featured-image {
            width: 100%;
            height: auto;
            display: block;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .featured-image:hover {
            opacity: 0.95;
        }
        
        .thumbnails-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .thumbnail-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f4f6f8;
            border: 1px solid #e1e5ea;
        }
        
        .thumbnail-item {
            width: 100%;
            height: 100%;
            object-fit: contain;
            cursor: pointer;
            transition: var(--transition);
        }

        .more-images-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
            z-index: 10;
        }
        
        .post-content-container {
            position: relative;
            margin-bottom: 1rem;
            padding: 0 1.25rem;
        }

        .post-content-text {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 4; /* Mostrar solo 4 líneas inicialmente */
            -webkit-box-orient: vertical;
            transition: max-height 0.3s ease;
            line-height: 1.6;
        }

        .post-content-text.expanded {
            display: block;
            -webkit-line-clamp: initial;
        }

        .read-more-btn {
            background: none;
            border: none;
            color: var(--primary);
            font-weight: 600;
            padding: 0;
            margin-top: 0.5rem;
            cursor: pointer;
            font-size: 0.9rem;
            display: none; /* Se mostrará vía JS si el texto es largo */
        }

        .read-more-btn:hover {
            text-decoration: underline;
        }

        /* Visor de PDF */
        .pdf-viewer-container {
            margin: 0.5rem 1.25rem 1.25rem;
            background: #2f3542;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .pdf-header {
            background: #2f3542;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .pdf-title {
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pdf-title i { color: #ff4757; font-size: 1.2rem; }

        .pdf-actions { display: flex; gap: 15px; font-size: 1.1rem; }
        .pdf-actions i { cursor: pointer; transition: 0.2s; opacity: 0.8; }
        .pdf-actions i:hover { opacity: 1; transform: scale(1.1); }

        .pdf-embed {
            width: 100%;
            height: 500px;
            border: none;
            background: white;
        }

        /* Sección de Documentos Adicionales */
        .additional-docs-section {
            padding: 0 1.25rem 1.5rem;
            margin-top: 1rem;
        }

        .section-title {
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 1rem;
            display: block;
        }

        .docs-column {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .doc-card {
            background: #fffafa;
            border: 1px solid #fee2e2;
            border-radius: 12px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: var(--transition);
        }

        .doc-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 71, 87, 0.08);
            border-color: #ff4757;
        }

        .doc-info-wrapper {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
            min-width: 0;
        }

        .doc-icon-box {
            width: 45px;
            height: 45px;
            background: #ff4757;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .doc-details {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .doc-filename {
            font-weight: 700;
            font-size: 0.95rem;
            color: #2d3436;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .doc-meta {
            font-size: 0.75rem;
            color: #ff4757;
            font-weight: 600;
            display: flex;
            gap: 8px;
            text-transform: uppercase;
        }

        .doc-meta .destacado-tag {
            color: #ff4757;
            background: rgba(255, 71, 87, 0.1);
            padding: 1px 6px;
            border-radius: 4px;
        }

        .doc-actions-btns {
            display: flex;
            gap: 10px;
        }

        .action-circle {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #f1f2f6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #57606f;
            cursor: pointer;
            transition: 0.2s;
            border: 1px solid #dfe4ea;
        }

        .action-circle:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .action-circle.is-destacado {
            background: #ff4757;
            color: white;
            border-color: #ff4757;
        }
    </style>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Admin API</h2>
        </div>
        <nav class="nav-links">
            <a href="index.php" class="nav-link <?php echo $page == 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-chart-line" style="margin-right:0.75rem;"></i> Dashboard</a>
            <a href="nueva_publicacion.php" class="nav-link <?php echo $page == 'nueva_p' ? 'active' : ''; ?>"><i class="fas fa-plus-circle" style="margin-right:0.75rem;"></i> Nueva Publicación</a>
            <a href="clientes.php" class="nav-link <?php echo $page == 'clientes' ? 'active' : ''; ?>"><i class="fas fa-building" style="margin-right:0.75rem;"></i> Clientes & API Keys</a>
            <a href="publicaciones.php" class="nav-link <?php echo $page == 'publicaciones' ? 'active' : ''; ?>"><i class="fas fa-eye" style="margin-right:0.75rem;"></i> Monitor Global</a>
            <a href="gestion_publicaciones.php" class="nav-link <?php echo $page == 'gestion_publicaciones' ? 'active' : ''; ?>"><i class="fas fa-tasks" style="margin-right:0.75rem;"></i> Gestión Centralizada</a>
            <a href="docs.php" class="nav-link <?php echo $page == 'docs' ? 'active' : ''; ?>"><i class="fas fa-book" style="margin-right:0.75rem;"></i> Documentación</a>
            <a href="download_spec.php" class="nav-link <?php echo $page == 'download_spec' ? 'active' : ''; ?>" style="color:#10b981;"><i class="fas fa-robot" style="margin-right:0.75rem;"></i> Descargar IA Spec</a>
            <a href="perfil.php" class="nav-link <?php echo $page == 'perfil' ? 'active' : ''; ?>"><i class="fas fa-user-cog" style="margin-right:0.75rem;"></i> Mi Perfil</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn">Cerrar Sesión (<?php echo $_SESSION['admin_username']; ?>)</a>
        </div>
    </div>
    <div class="main-content">
