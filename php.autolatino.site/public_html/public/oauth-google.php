<?php
/**
 * MotoSpot — Google OAuth callback + redirect
 * URL configurada en Google Console: https://php.autolatino.site/oauth-google.php
 */
defined('MOTO_SPOT') || define('MOTO_SPOT', true);

require_once __DIR__ . '/../includes/env.php';
loadEnv();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/logger.php';
require_once __DIR__ . '/../includes/google_oauth.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// ── Paso 1: No hay code → redirigir a Google ─────────────────────────────────
if (!isset($_GET['code'])) {
    header('Location: ' . googleAuthUrl());
    exit();
}

// ── Paso 2: Validar state (protección CSRF) ───────────────────────────────────
if (empty($_GET['state']) || $_GET['state'] !== ($_SESSION['google_oauth_state'] ?? '')) {
    logWarning('[oauth-google] State inválido — posible CSRF');
    header('Location: /login.php?error=oauth_invalid');
    exit();
}
unset($_SESSION['google_oauth_state']);

// ── Paso 3: Intercambiar code por token ───────────────────────────────────────
$tokens = googleExchangeCode($_GET['code']);
if (!$tokens) {
    logWarning('[oauth-google] No se pudo obtener access_token');
    header('Location: /login.php?error=oauth_token');
    exit();
}

// ── Paso 4: Obtener datos del usuario de Google ───────────────────────────────
$googleUser = googleGetUser($tokens['access_token']);
if (!$googleUser) {
    logWarning('[oauth-google] No se pudo obtener datos del usuario');
    header('Location: /login.php?error=oauth_user');
    exit();
}

// ── Paso 5: Encontrar o crear usuario en BD ───────────────────────────────────
$usuario = googleFindOrCreateUser($googleUser);
if (!$usuario) {
    logError('[oauth-google] No se pudo crear/encontrar usuario: ' . $googleUser['email']);
    header('Location: /login.php?error=oauth_db');
    exit();
}

// ── Paso 6: Verificar que la cuenta no esté suspendida ───────────────────────
if (($usuario['estado'] ?? '') === 'suspendido') {
    header('Location: /login.php?error=cuenta_suspendida');
    exit();
}

// ── Paso 7: Iniciar sesión ────────────────────────────────────────────────────
$_SESSION['usuario_id']     = $usuario['id'];
$_SESSION['usuario_nombre'] = $usuario['nombre'];
$_SESSION['usuario_email']  = $usuario['email'];
$_SESSION['usuario_tipo']   = $usuario['tipo'];
$_SESSION['auth_provider']  = 'google';

// Actualizar último acceso
try {
    getDB()->prepare("UPDATE ms_usuarios SET ultimo_acceso = NOW() WHERE id = ?")
           ->execute([$usuario['id']]);
} catch (Throwable) {}

logInfo('[oauth-google] Login exitoso: ' . $usuario['email']);

$redirect = $_SESSION['oauth_redirect'] ?? '/';
unset($_SESSION['oauth_redirect']);

header('Location: ' . $redirect);
exit();
