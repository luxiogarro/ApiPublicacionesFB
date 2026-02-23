<?php
/**
 * Convierte una URL de video (YouTube, Vimeo, Facebook) en un iframe embed.
 */
function getEmbedUrl($url) {
    if (empty($url)) return null;

    // Si ya es un iframe, lo dejamos pasar directamente
    if (strpos($url, '<iframe') !== false) {
        return $url;
    }

    // YouTube
    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
        return '<iframe src="https://www.youtube.com/embed/' . $match[1] . '" allowfullscreen></iframe>';
    }

    // Vimeo
    if (preg_match('%https?://(?:www\.)?vimeo\.com/(?:channels/(?:\w+/)?|groups/([^/]*)/videos/|album/(\d+)/video/|video/|)(\d+)(?:$|/|\?)%i', $url, $match)) {
        return '<iframe src="https://player.vimeo.com/video/' . $match[3] . '" allowfullscreen></iframe>';
    }

    // Facebook
    if (strpos($url, 'facebook.com') !== false) {
        return '<iframe src="https://www.facebook.com/plugins/video.php?href=' . urlencode($url) . '&show_text=0&width=560" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>';
    }

    return null;
}
