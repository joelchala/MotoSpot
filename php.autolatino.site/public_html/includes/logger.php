<?php
/**
 * MotoSpot - Logger simple a archivo
 * Sin dependencias. Escribe en storage/logs/ fuera de public_html.
 * Niveles: debug | info | warning | error | critical
 */

if (!defined('MOTO_SPOT')) {
    die('Acceso no autorizado');
}

class Logger
{
    private static string $logDir  = '';
    private static string $logFile = '';

    private static array $levels = [
        'debug'    => 0,
        'info'     => 1,
        'warning'  => 2,
        'error'    => 3,
        'critical' => 4,
    ];

    /**
     * Inicializa el logger. Se llama una vez desde config/bootstrap.
     */
    public static function init(): void
    {
        // Ruta desde .env o fallback dentro de public_html (menos ideal)
        $base = env('LOG_PATH', dirname(__DIR__) . '/storage/logs');

        self::$logDir  = rtrim($base, '/');
        self::$logFile = self::$logDir . '/app-' . date('Y-m-d') . '.log';

        // Crear directorio si no existe
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
    }

    // ── Métodos de conveniencia ──────────────────────────────────────────

    public static function debug(string $message, array $context = []): void
    {
        self::write('DEBUG', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function critical(string $message, array $context = []): void
    {
        self::write('CRITICAL', $message, $context);
    }

    // ── Motor interno ────────────────────────────────────────────────────

    private static function write(string $level, string $message, array $context = []): void
    {
        // Respetar nivel mínimo configurado en .env
        $minLevel = strtolower(env('LOG_LEVEL', 'error'));
        $minInt   = self::$levels[$minLevel] ?? 3;
        $curInt   = self::$levels[strtolower($level)] ?? 3;

        if ($curInt < $minInt) return;

        // Inicializar si aún no se hizo
        if (self::$logFile === '') {
            self::init();
        }

        // Formatear contexto
        $ctx = empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);

        // Línea de log: [2026-03-29 14:00:00] [ERROR] Mensaje {contexto}
        $line = sprintf(
            "[%s] [%s] %s%s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            $ctx
        );

        // Escribir a archivo (append)
        file_put_contents(self::$logFile, $line, FILE_APPEND | LOCK_EX);

        // También enviar al log del sistema para errores críticos
        if ($curInt >= self::$levels['error']) {
            error_log('[MotoSpot] ' . $level . ': ' . $message);
        }
    }

    /**
     * Rotar logs: elimina archivos más viejos de N días
     */
    public static function rotate(int $keepDays = 14): void
    {
        if (!is_dir(self::$logDir)) return;

        foreach (glob(self::$logDir . '/app-*.log') as $file) {
            if (filemtime($file) < time() - ($keepDays * 86400)) {
                unlink($file);
            }
        }
    }
}

// ── Funciones de conveniencia globales ──────────────────────────────────────
function logDebug(string $msg, array $ctx = []): void    { Logger::debug($msg, $ctx); }
function logInfo(string $msg, array $ctx = []): void     { Logger::info($msg, $ctx); }
function logWarning(string $msg, array $ctx = []): void  { Logger::warning($msg, $ctx); }
function logError(string $msg, array $ctx = []): void    { Logger::error($msg, $ctx); }
function logCritical(string $msg, array $ctx = []): void { Logger::critical($msg, $ctx); }
?>
