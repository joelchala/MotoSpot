<?php
/**
 * MotoSpot — Cron: Rotación de logs
 * Ejecutar una vez a la semana desde hPanel:
 *   php /home/u986675534/domains/php.autolatino.site/public_html/cron/rotate_logs.php
 *
 * Elimina archivos .log con más de LOG_RETENTION_DAYS días de antigüedad.
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Acceso denegado');
}

define('MOTOSPOT_CRON', true);
define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/includes/env.php';
require_once ROOT_PATH . '/includes/logger.php';

loadEnv();

const LOG_RETENTION_DAYS = 30;

logInfo('[cron:logs] Iniciando rotación de logs');

$logPath = rtrim(env('LOG_PATH', ''), '/');

if (!is_dir($logPath)) {
    logWarning("[cron:logs] Directorio de logs no existe: $logPath");
    exit(1);
}

$limite     = time() - (LOG_RETENTION_DAYS * 86400);
$eliminados = 0;
$liberados  = 0; // bytes

try {
    $archivos = new DirectoryIterator($logPath);

    foreach ($archivos as $archivo) {
        if ($archivo->isDot() || !$archivo->isFile()) continue;
        if ($archivo->getExtension() !== 'log') continue;

        $mtime = $archivo->getMTime();

        if ($mtime < $limite) {
            $bytes    = $archivo->getSize();
            $nombre   = $archivo->getFilename();
            $ruta     = $archivo->getRealPath();

            if (unlink($ruta)) {
                $eliminados++;
                $liberados += $bytes;
                logInfo("[cron:logs] Eliminado: $nombre (" . humanBytes($bytes) . ')');
            } else {
                logWarning("[cron:logs] No se pudo eliminar: $nombre");
            }
        }
    }

    $resumen = "[cron:logs] Fin: $eliminados archivos eliminados, " . humanBytes($liberados) . ' liberados';
    logInfo($resumen);
    echo $resumen . PHP_EOL;

} catch (Throwable $e) {
    logCritical('[cron:logs] Error fatal: ' . $e->getMessage());
    exit(1);
}

function humanBytes(int $bytes): string
{
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}
