<?php
/**
 * admin/ajax_load_posts.php
 * Carga más publicaciones para el infinite scroll del monitor global.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Model/Post.php';
require_once __DIR__ . '/includes/utils.php';

use App\Model\Post;

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

$filters = [
    'search' => $_GET['search'] ?? null,
    'cliente_id' => $_GET['cliente_id'] ?? null,
    'date_from' => $_GET['date_from'] ?? null,
    'date_to' => $_GET['date_to'] ?? null
];

$posts = Post::getAllGlobal($limit, $offset, $filters);

if (empty($posts)) {
    exit; // No hay más posts
}

foreach ($posts as $p) {
    include __DIR__ . '/includes/post_card.php';
}
