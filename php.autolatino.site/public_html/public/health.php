<?php
/**
 * MotoSpot - Health Check
 * Verifica el estado del servidor, PHP y base de datos.
 * Acceso: /health.php?token=ms_check_2026
 */

define('MOTO_SPOT', true);
require_once __DIR__ . '/../includes/env.php';
loadEnv();

// Token de acceso (evita exposición pública)
$token = $_GET['token'] ?? '';
if ($token !== 'ms_check_2026') {
    http_response_code(403);
    die(json_encode(['status' => 'forbidden']));
}

$checks  = [];
$allOk   = true;
$format  = $_GET['format'] ?? 'html'; // ?format=json para APIs/cron

// ── PHP Version ──────────────────────────────────────────────────────────────
$phpOk        = version_compare(PHP_VERSION, '8.0.0', '>=');
$checks['php'] = [
    'ok'    => $phpOk,
    'value' => PHP_VERSION,
    'note'  => $phpOk ? 'OK' : 'Se requiere PHP 8.0+',
];
if (!$phpOk) $allOk = false;

// ── Extensiones PHP ──────────────────────────────────────────────────────────
$requiredExt = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'fileinfo'];
foreach ($requiredExt as $ext) {
    $loaded = extension_loaded($ext);
    $checks["ext_$ext"] = ['ok' => $loaded, 'value' => $loaded ? 'Cargada' : 'FALTA'];
    if (!$loaded) $allOk = false;
}

// ── Conexión a base de datos ─────────────────────────────────────────────────
try {
    $dsn  = 'mysql:host=' . env('DB_HOST', 'localhost')
          . ';dbname='    . env('DB_NAME', '')
          . ';charset=utf8mb4';
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if (defined('PDO::MYSQL_ATTR_CONNECT_TIMEOUT')) {
        $opts[PDO::MYSQL_ATTR_CONNECT_TIMEOUT] = 5;
    }
    $pdo     = new PDO($dsn, env('DB_USER'), env('DB_PASS'), $opts);
    $version = $pdo->query('SELECT VERSION() AS v')->fetchColumn();
    $checks['database'] = ['ok' => true, 'value' => 'MySQL ' . $version];
} catch (Throwable $e) {
    $checks['database'] = ['ok' => false, 'value' => 'FALLO: ' . $e->getMessage()];
    $allOk = false;
}

// ── Directorio de uploads ────────────────────────────────────────────────────
$uploadPath = env('UPLOAD_PATH', __DIR__ . '/../uploads');
$uploadOk   = is_dir($uploadPath) && is_writable($uploadPath);
$checks['uploads'] = [
    'ok'    => $uploadOk,
    'value' => $uploadPath,
    'note'  => $uploadOk ? 'Escribible' : 'No existe o sin permisos',
];
if (!$uploadOk) $allOk = false;

// ── Directorio de logs ───────────────────────────────────────────────────────
$logPath  = env('LOG_PATH', __DIR__ . '/../storage/logs');
$logOk    = (is_dir($logPath) && is_writable($logPath)) || mkdir($logPath, 0755, true);
$checks['logs'] = [
    'ok'    => $logOk,
    'value' => $logPath,
    'note'  => $logOk ? 'Escribible' : 'Sin permisos de escritura',
];

// ── Respuesta JSON ───────────────────────────────────────────────────────────
if ($format === 'json') {
    http_response_code($allOk ? 200 : 503);
    header('Content-Type: application/json');
    echo json_encode([
        'status'    => $allOk ? 'ok' : 'degraded',
        'timestamp' => date('c'),
        'checks'    => $checks,
    ], JSON_PRETTY_PRINT);
    exit();
}

// ── Respuesta HTML ───────────────────────────────────────────────────────────
http_response_code($allOk ? 200 : 503);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MotoSpot — Health Check</title>
    <style>
        body { font-family: monospace; background: #111; color: #eee; padding: 24px; margin: 0; }
        h2 { color: #60a5fa; }
        table { border-collapse: collapse; width: 100%; margin-top: 12px; }
        th, td { border: 1px solid #374151; padding: 8px 14px; text-align: left; }
        th { background: #1f2937; }
        .ok   { color: #4ade80; font-weight: bold; }
        .fail { color: #f87171; font-weight: bold; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 1rem; font-weight: bold; }
        .badge-ok   { background: #166534; color: #4ade80; }
        .badge-fail { background: #7f1d1d; color: #f87171; }
        small { color: #9ca3af; }
    </style>
</head>
<body>
    <h2>🔍 MotoSpot — Health Check</h2>
    <p>
        Estado general:
        <span class="badge <?php echo $allOk ? 'badge-ok' : 'badge-fail'; ?>">
            <?php echo $allOk ? '✅ OPERATIVO' : '⚠️ DEGRADADO'; ?>
        </span>
        <small style="margin-left:12px;"><?php echo date('Y-m-d H:i:s T'); ?></small>
    </p>

    <table>
        <tr><th>Componente</th><th>Estado</th><th>Detalle</th></tr>
        <?php foreach ($checks as $name => $check): ?>
            <tr>
                <td><?php echo htmlspecialchars($name); ?></td>
                <td class="<?php echo $check['ok'] ? 'ok' : 'fail'; ?>">
                    <?php echo $check['ok'] ? '✅ OK' : '❌ FALLO'; ?>
                </td>
                <td>
                    <?php echo htmlspecialchars($check['value']); ?>
                    <?php if (!empty($check['note'])): ?>
                        <small> — <?php echo htmlspecialchars($check['note']); ?></small>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <p style="color:#6b7280; margin-top:32px; font-size:0.85rem;">
        ⚠️ Protegido con token. No exponer públicamente.
        | <a href="?token=ms_check_2026&format=json" style="color:#60a5fa;">Ver en JSON</a>
    </p>
</body>
</html>
