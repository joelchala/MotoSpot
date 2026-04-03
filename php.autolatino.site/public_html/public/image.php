<?php
/**
 * MotoSpot - Servidor seguro de imágenes
 * Sirve archivos desde fuera de public_html validando permisos.
 * Uso: /image.php?f=vehiculos/abc123.jpg
 *
 * Cuando uploads/ esté DENTRO de public_html, este archivo actúa como
 * proxy de compatibilidad. Cuando se mueva fuera, solo hay que actualizar
 * UPLOAD_PATH en .env.
 */

define('MOTO_SPOT', true);
require_once __DIR__ . '/../includes/env.php';
loadEnv();

// ── Parámetros ───────────────────────────────────────────────────────────────
$file = $_GET['f'] ?? '';

// Validar que no haya path traversal
if (empty($file) || str_contains($file, '..') || str_contains($file, "\0")) {
    http_response_code(400);
    exit('Solicitud inválida.');
}

// Solo permitir rutas relativas sin barra al inicio
$file = ltrim($file, '/\\');

// Solo permitir extensiones de imagen
$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($ext, $allowedExtensions, true)) {
    http_response_code(403);
    exit('Tipo de archivo no permitido.');
}

// ── Resolver ruta física ─────────────────────────────────────────────────────
$uploadBase = rtrim(env('UPLOAD_PATH', __DIR__ . '/../uploads'), '/');
$fullPath   = $uploadBase . '/' . $file;

// Verificar que el path resuelto sigue dentro del directorio base (anti-traversal)
$realBase = realpath($uploadBase);
$realFile = realpath($fullPath);

if ($realBase === false || $realFile === false || !str_starts_with($realFile, $realBase)) {
    http_response_code(403);
    exit('Acceso denegado.');
}

if (!is_file($realFile)) {
    http_response_code(404);
    exit('Imagen no encontrada.');
}

// ── Servir la imagen ─────────────────────────────────────────────────────────
$mimeTypes = [
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
];

$mime    = $mimeTypes[$ext] ?? 'application/octet-stream';
$size    = filesize($realFile);
$mtime   = filemtime($realFile);
$etag    = '"' . md5($realFile . $mtime) . '"';

// Cache headers (imágenes no cambian frecuentemente)
header('Content-Type: ' . $mime);
header('Content-Length: ' . $size);
header('Cache-Control: public, max-age=2592000'); // 30 días
header('ETag: ' . $etag);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');

// 304 Not Modified si el cliente ya tiene la imagen en caché
if (
    (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag) ||
    (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $mtime)
) {
    http_response_code(304);
    exit();
}

readfile($realFile);
exit();
?>
