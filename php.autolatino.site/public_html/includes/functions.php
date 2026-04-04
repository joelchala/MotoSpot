<?php
/**
 * MotoSpot - Funciones Auxiliares
 */

// Prevenir acceso directo
if (!defined('MOTO_SPOT')) {
    die('Acceso no autorizado');
}

// Cargar configuración
$config = require __DIR__ . '/config.php';

/**
 * Obtiene un valor de configuración
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getConfig($key, $default = null) {
    global $config;
    return $config[$key] ?? $default;
}

/**
 * Obtiene la conexión global a la base de datos
 * @return PDO
 */
function getDB() {
    global $pdo;
    if (!isset($pdo)) {
        throw new Exception("Base de datos no inicializada");
    }
    return $pdo;
}

/**
 * Función auxiliar para realizar consultas seguras
 * @param string $sql
 * @param array $params
 * @return PDOStatement
 */
function query($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Obtiene un solo registro
 * @param string $sql
 * @param array $params
 * @return array|null
 */
function fetchOne($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->fetch() ?: null;
}

/**
 * Obtiene múltiples registros
 * @param string $sql
 * @param array $params
 * @return array
 */
function fetchAll($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Escapa HTML para prevenir XSS
 * @param string $text
 * @return string
 */
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Genera URL amigable
 * @param string $text
 * @return string
 */
function slugify($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * Ejecuta una consulta SQL con parámetros y devuelve el statement
 * @param string $sql
 * @param array $params
 * @return PDOStatement
 */
function executeQuery($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Devuelve el último ID insertado en la base de datos
 * @return string
 */
function getLastInsertId() {
    global $pdo;
    return $pdo->lastInsertId();
}

/**
 * Inicia una transacción PDO
 */
function beginTransaction() {
    global $pdo;
    $pdo->beginTransaction();
}

/**
 * Confirma una transacción PDO
 */
function commitTransaction() {
    global $pdo;
    $pdo->commit();
}

/**
 * Revierte una transacción PDO
 */
function rollbackTransaction() {
    global $pdo;
    $pdo->rollBack();
}

// ── FUNCIONES DE VALIDACIÓN ──────────────────────────────────────────

/**
 * Valida un string - verifica longitud mínima y máxima
 * @param string $value
 * @param int $min
 * @param int $max
 * @return bool
 */
function validarString($value, $min = 1, $max = 255) {
    $value = trim($value ?? '');
    $len = strlen($value);
    return $len >= $min && $len <= $max;
}

/**
 * Valida un número entero dentro de rango
 * @param int $value
 * @param int $min
 * @param int $max
 * @return bool
 */
function validarInt($value, $min = null, $max = null) {
    $value = intval($value ?? 0);
    if ($min !== null && $value < $min) return false;
    if ($max !== null && $value > $max) return false;
    return true;
}

/**
 * Valida un número flotante dentro de rango
 * @param float $value
 * @param float $min
 * @param float $max
 * @return bool
 */
function validarFloat($value, $min = null, $max = null) {
    $value = floatval($value ?? 0);
    if ($min !== null && $value < $min) return false;
    if ($max !== null && $value > $max) return false;
    return true;
}

/**
 * Valida un email
 * @param string $email
 * @return bool
 */
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida que un valor esté en una lista de opciones permitidas
 * @param string $value
 * @param array $allowedValues
 * @return bool
 */
function validarEnum($value, array $allowedValues) {
    return in_array($value, array_keys($allowedValues), true);
}

/**
 * Valida un año - debe ser entre 1900 y año actual + 1
 * @param int $year
 * @return bool
 */
function validarAno($year) {
    $year = intval($year ?? 0);
    return $year >= 1900 && $year <= date('Y') + 1;
}
?>
/**
 * Valida y sanitiza una URL interna (redirección segura)
 * Solo permite rutas internas comenzando con /
 * @param string $url
 * @param array $allowed Rutas permitidas (whitelist)
 * @return string URL validada o '/' por defecto
 */
function validarURL($url, $allowed = []) {
    if (empty($url)) return '/';
    
    // Rechazar URLs con protocolo
    if (strpos($url, '://') !== false) return '/';
    
    // Rechazar protocolo-relative URLs (//)
    if (str_starts_with($url, '//')) return '/';
    
    // Debe empezar con /
    if (!str_starts_with($url, '/')) return '/';
    
    // Si hay whitelist, validar contra ella
    if (!empty($allowed)) {
        $parsedPath = parse_url($url, PHP_URL_PATH);
        if (!in_array($parsedPath, $allowed, true)) {
            return '/';
        }
    }
    
    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}

/**
 * Valida un número de teléfono (formato básico)
 * Permite: +34 123 456 7890, (123) 456-7890, 123.456.7890, 1234567890
 * @param string $phone
 * @return bool
 */
function validarTelefono($phone) {
    // Remover espacios, guiones, puntos, paréntesis
    $clean = preg_replace('/[\s\-().]+/', '', $phone ?? '');
    
    // Debe ser +XXX... o XXX...
    // Mínimo 10 dígitos, máximo 15
    return preg_match('/^\+?[0-9]{10,15}$/', $clean) === 1;
}

/**
 * Valida una contraseña según políticas de seguridad
 * @param string $password
 * @param int $minLength Longitud mínima
 * @return array ['valid' => bool, 'error' => string (opcional)]
 */
function validarPasswordSegura($password, $minLength = 12) {
    if (strlen($password) < $minLength) {
        return [
            'valid' => false,
            'error' => "Mínimo $minLength caracteres requeridos"
        ];
    }
    
    // Validar complejidad (al menos 3 de 4 criterios)
    $criteria = [
        'uppercase' => preg_match('/[A-Z]/', $password),
        'lowercase' => preg_match('/[a-z]/', $password),
        'numbers'   => preg_match('/[0-9]/', $password),
        'special'   => preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password),
    ];
    
    $passCount = array_sum($criteria);
    if ($passCount < 3) {
        return [
            'valid' => false,
            'error' => 'Debe incluir mayúsculas, minúsculas, números y caracteres especiales'
        ];
    }
    
    return ['valid' => true];
}

/**
 * Obtiene el tipo MIME de un archivo de forma segura
 * @param string $filepath
 * @return string|false
 */
function getMimeType($filepath) {
    // Preferir finfo (más seguro que mime_content_type deprecated)
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filepath);
        finfo_close($finfo);
        return $mime;
    }
    
    // Fallback a mime_content_type si está disponible
    if (function_exists('mime_content_type')) {
        return mime_content_type($filepath);
    }
    
    return false;
}

/**
 * Genera un nombre de archivo seguro usando random_bytes
 * @param string $originalName Nombre original del archivo
 * @param array $allowedExt Extensiones permitidas ['jpg', 'png', ...]
 * @return array ['valid' => bool, 'filename' => string (opcional), 'error' => string (opcional)]
 */
function generarNombreArchivoSeguro($originalName, $allowedExt = []) {
    // Validar nombre no vacío
    if (empty($originalName)) {
        return ['valid' => false, 'error' => 'Nombre de archivo vacío'];
    }
    
    // Obtener extensión
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    
    // Validar extensión si hay whitelist
    if (!empty($allowedExt) && !in_array($ext, $allowedExt, true)) {
        return ['valid' => false, 'error' => "Extensión no permitida: $ext"];
    }
    
    // Generar nombre seguro con random_bytes
    $randomName = bin2hex(random_bytes(16));
    $filename = $randomName . '.' . $ext;
    
    return ['valid' => true, 'filename' => $filename];
}
?>

/**
 * Polyfill para str_starts_with() - disponible en PHP 8.0+
 * Soporta compatibilidad con PHP 7.x si es necesario
 */
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return strpos($haystack, $needle) === 0;
    }
}

/**
 * Polyfill para str_ends_with() - disponible en PHP 8.0+
 */
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        return strlen($needle) === 0 || strrpos($haystack, $needle) === strlen($haystack) - strlen($needle);
    }
}

/**
 * Polyfill para str_contains() - disponible en PHP 8.0+
 */
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return strpos($haystack, $needle) !== false;
    }
}
?>
