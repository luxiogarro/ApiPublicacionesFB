<?php
namespace App\Core;

class ImageOptimizer {
    /**
     * Optimiza una imagen: la reescala si es necesario y la convierte a WebP.
     * 
     * @param string $tmpPath Ruta del archivo temporal.
     * @param string $originalName Nombre original del archivo para detectar extensión.
     * @return string|bool El nuevo nombre del archivo optimizado o false si falló.
     */
    public static function optimize($tmpPath, $originalName) {
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($ext, $allowed)) {
            return false; // No es una imagen procesable
        }

        // Crear recurso de imagen según el tipo
        $image = null;
        try {
            switch ($ext) {
                case 'jpg':
                case 'jpeg': $image = @imagecreatefromjpeg($tmpPath); break;
                case 'png': $image = @imagecreatefrompng($tmpPath); break;
                case 'gif': $image = @imagecreatefromgif($tmpPath); break;
                case 'webp': $image = @imagecreatefromwebp($tmpPath); break;
            }
        } catch (\Exception $e) {
            return false;
        }

        if (!$image) return false;

        // Obtener dimensiones originales
        $width = imagesx($image);
        $height = imagesy($image);
        $maxWidth = 1200;

        // Reescalar si es más ancha que el máximo permitido
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) floor($height * ($maxWidth / $width));
            $tmpImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Mantener transparencia para PNG y WebP
            imagealphablending($tmpImage, false);
            imagesavealpha($tmpImage, true);
            
            imagecopyresampled($tmpImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $tmpImage;
        }

        $newFilename = uniqid() . '.webp';
        $destination = UPLOAD_DIR . $newFilename;

        // Guardar como WebP con calidad 80 (balance óptimo entre peso y calidad)
        if (imagewebp($image, $destination, 80)) {
            imagedestroy($image);
            return $newFilename;
        }

        imagedestroy($image);
        return false;
    }
}
