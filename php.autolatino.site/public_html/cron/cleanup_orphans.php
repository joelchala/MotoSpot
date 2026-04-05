<?php
/**
 * MotoSpot — Cron: Limpieza de imágenes huérfanas
 * Ejecutar una vez al día desde hPanel:
 *   php /home/u986675534/domains/php.autolatino.site/public_html/cron/cleanup_orphans.php
 *
 * Elimina archivos de imagen en storage/uploads/ que no estén
 * referenciados en ninguna fila de ms_vehiculo_imagenes.
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Acceso denegado');
}

define('MOTOSPOT_CRON', true);
define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/includes/env.php';
require_once ROOT_PATH . '/includes/logger.php';
require_once ROOT_PATH . '/includes/db.php';

loadEnv();

logInfo('[cron:cleanup] Iniciando limpieza de imágenes huérfanas');

$uploadPath = rtrim(env('UPLOAD_PATH', ''), '/');

if (!is_dir($uploadPath)) {
    logWarning("[cron:cleanup] Directorio de uploads no existe: $uploadPath");
    exit(1);
}

try {
    $pdo = getDB();

    // Obtener todos los nombres de archivo registrados en BD
    $stmt = $pdo->query("SELECT url_foto FROM ms_vehiculo_fotos");
    $registradas = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Guardar solo el basename para comparar con archivos en disco
        $registradas[basename($row['url_foto'])] = true;
    }

    logInfo('[cron:cleanup] ' . count($registradas) . ' imágenes registradas en BD');

    // Recorrer archivos en disco
    $archivos  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploadPath));
    $eliminados = 0;
    $omitidos   = 0;

    foreach ($archivos as $archivo) {
        if (!$archivo->isFile()) continue;

        $nombre    = $archivo->getFilename();
        $extension = strtolower($archivo->getExtension());

        // Solo procesar imágenes
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            continue;
        }

        if (isset($registradas[$nombre])) {
            $omitidos++;
            continue;
        }

        // Archivo huérfano — verificar antigüedad (> 24h para no borrar uploads en progreso)
        $mtime = $archivo->getMTime();
        if (time() - $mtime < 86400) {
            logInfo("[cron:cleanup] Omitido (reciente): $nombre");
            $omitidos++;
            continue;
        }

        $rutaCompleta = $archivo->getRealPath();
        if (unlink($rutaCompleta)) {
            logInfo("[cron:cleanup] Eliminado huérfano: $nombre");
            $eliminados++;
        } else {
            logWarning("[cron:cleanup] No se pudo eliminar: $nombre");
        }
    }

    logInfo("[cron:cleanup] Fin: $eliminados eliminados, $omitidos conservados");

} catch (Throwable $e) {
    logCritical('[cron:cleanup] Error fatal: ' . $e->getMessage());
    exit(1);
}
