<?php
/**
 * Motor de descarga multipropósito
 */
$sourceFile = __DIR__ . DIRECTORY_SEPARATOR . 'ia-spec.md';
$format = isset($_GET['format']) ? $_GET['format'] : 'md';

if (!file_exists($sourceFile)) {
    die("Error: Archivo fuente no encontrado.");
}

// Limpiar salida
if (ob_get_level()) ob_end_clean();

switch ($format) {
    case 'zip':
        $zipFile = sys_get_temp_dir() . '/spec_' . time() . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($sourceFile, 'API_SPECIFICATION.md');
            $zip->close();
            
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="API_SPECIFICATION.zip"');
            header('Content-Length: ' . filesize($zipFile));
            readfile($zipFile);
            unlink($zipFile);
            exit;
        }
        die("Error: No se pudo crear el ZIP.");
        
    case 'txt':
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="API_SPECIFICATION.txt"');
        break;
        
    default:
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="API_SPECIFICATION.md"; filename*=UTF-8\'\'API_SPECIFICATION.md');
}

header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($sourceFile));

readfile($sourceFile);
exit;
