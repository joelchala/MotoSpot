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