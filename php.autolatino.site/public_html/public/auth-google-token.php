<?php
/**
 * MotoSpot — Google Identity Services token endpoint
 * Recibe credential (JWT) por POST, lo verifica con Google y crea sesión.
 * NO usa redirect/callback — evita problemas con ModSecurity.
 */
defined('MOTO_SPOT') || define('MOTO_SPOT', true);

require_once __DIR__ . '/../includes/env.php';
loadEnv();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/logger.php';

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

$body       = json_decode(file_get_contents('php://input'), true);
$credential = trim($body['credential'] ?? '');

if (!$credential) {
    http_response_code(400);
    echo json_encode(['error' => 'Token requerido']);
    exit();
}

// ── Verificar el ID token con Google tokeninfo endpoint ───────────────────────
$ch = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($credential));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_SSL_VERIFYPEER => true,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    logWarning('[auth-google-token] Token inválido, Google respondió: ' . $httpCode);
    http_response_code(401);
    echo json_encode(['error' => 'Token de Google inválido o expirado']);
    exit();
}

$payload = json_decode($response, true);

// Validar que el token es para nuestra app
$clientId = env('GOOGLE_CLIENT_ID');
if (($payload['aud'] ?? '') !== $clientId) {
    logWarning('[auth-google-token] aud no coincide: ' . ($payload['aud'] ?? 'none'));
    http_response_code(401);
    echo json_encode(['error' => 'Token no pertenece a esta aplicación']);
    exit();
}

$googleId = $payload['sub']            ?? null;
$email    = $payload['email']          ?? null;
$nombre   = $payload['given_name']     ?? ($payload['name'] ?? '');
$apellido = $payload['family_name']    ?? '';
$avatar   = $payload['picture']        ?? null;

if (!$googleId || !$email) {
    http_response_code(401);
    echo json_encode(['error' => 'No se pudo obtener datos del usuario']);
    exit();
}

try {
    $pdo = getDB();

    // Buscar por google_id primero
    $stmt = $pdo->prepare("SELECT * FROM ms_usuarios WHERE google_id = ? LIMIT 1");
    $stmt->execute([$googleId]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Actualizar avatar si cambió
        if ($avatar && $usuario['avatar_url'] !== $avatar) {
            $pdo->prepare("UPDATE ms_usuarios SET avatar_url = ? WHERE id = ?")
                ->execute([$avatar, $usuario['id']]);
        }
    } else {
        // Buscar por email (cuenta existente con contraseña)
        $stmt = $pdo->prepare("SELECT * FROM ms_usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // Vincular Google a cuenta existente
            $pdo->prepare("UPDATE ms_usuarios SET google_id = ?, avatar_url = ?, auth_provider = 'google' WHERE id = ?")
                ->execute([$googleId, $avatar, $usuario['id']]);
        } else {
            // Crear nuevo usuario
            $stmt = $pdo->prepare("
                INSERT INTO ms_usuarios
                    (tipo, email, password, nombre, apellido, google_id, avatar_url, auth_provider, estado, email_verificado)
                VALUES
                    ('individual', ?, '', ?, ?, ?, ?, 'google', 'activo', 1)
            ");
            $stmt->execute([$email, $nombre, $apellido, $googleId, $avatar]);

            $stmt = $pdo->prepare("SELECT * FROM ms_usuarios WHERE id = ?");
            $stmt->execute([$pdo->lastInsertId()]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    if (!$usuario) {
        throw new RuntimeException('No se pudo crear/obtener usuario');
    }

    if (($usuario['estado'] ?? '') === 'suspendido') {
        http_response_code(403);
        echo json_encode(['error' => 'Cuenta suspendida']);
        exit();
    }

    // Iniciar sesión
    $_SESSION['usuario_id']     = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_email']  = $usuario['email'];
    $_SESSION['usuario_tipo']   = $usuario['tipo'];
    $_SESSION['auth_provider']  = 'google';

    $pdo->prepare("UPDATE ms_usuarios SET ultimo_acceso = NOW() WHERE id = ?")
        ->execute([$usuario['id']]);

    logInfo('[auth-google-token] Login exitoso: ' . $email);

    echo json_encode([
        'success'  => true,
        'redirect' => '/',
    ]);

} catch (Throwable $e) {
    logError('[auth-google-token] Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}
