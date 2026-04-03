<?php
/**
 * MotoSpot - Conexión a Base de Datos
 * Lee credenciales desde .env — sin hardcodear datos sensibles
 */

if (!defined('MOTO_SPOT')) {
    die('Acceso no autorizado');
}

// Cargar variables de entorno si aún no están cargadas
if (!function_exists('env')) {
    require_once __DIR__ . '/env.php';
}
if (!isset($_ENV['DB_HOST'])) {
    loadEnv();
}

// Leer configuración desde .env (con fallbacks seguros)
$dbConfig = [
    'host'      => env('DB_HOST', 'localhost'),
    'database'  => env('DB_NAME', ''),
    'username'  => env('DB_USER', ''),
    'password'  => env('DB_PASS', ''),
    'charset'   => env('DB_CHARSET', 'utf8mb4'),
    'prefix'    => env('DB_PREFIX', 'ms_'),
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];

// Timeout de socket (aplica a Unix socket y TCP)
ini_set('default_socket_timeout', 5);

// Agregar timeout de conexión MySQL solo si la extensión está disponible
if (defined('PDO::MYSQL_ATTR_CONNECT_TIMEOUT')) {
    $dbConfig['options'][PDO::MYSQL_ATTR_CONNECT_TIMEOUT] = 5;
}

try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
    $pdo->exec("SET time_zone = '-03:00'");

} catch (PDOException $e) {
    error_log('[MotoSpot][DB] Conexión fallida: ' . $e->getMessage());
    http_response_code(503);
    die('Error de conexión a la base de datos. Por favor, intente más tarde.');
} catch (Error $e) {
    error_log('[MotoSpot][DB] Error crítico: ' . $e->getMessage());
    http_response_code(503);
    die('Error interno del servidor. Por favor, intente más tarde.');
}

// Helper: tabla con prefijo
function table(string $name): string
{
    global $dbConfig;
    return $dbConfig['prefix'] . $name;
}
?>
