<?php
/**
 * MotoSpot — Google OAuth 2.0 helper
 * Sin Composer. Usa cURL puro.
 */

if (!defined('MOTO_SPOT')) define('MOTO_SPOT', true);

// ── URLs de Google ────────────────────────────────────────────────────────────
const GOOGLE_AUTH_URL  = 'https://accounts.google.com/o/oauth2/v2/auth';
const GOOGLE_TOKEN_URL = 'https://oauth2.googleapis.com/token';
const GOOGLE_USER_URL  = 'https://www.googleapis.com/oauth2/v3/userinfo';

// ── Generar URL de redirección a Google ───────────────────────────────────────
function googleAuthUrl(): string
{
    $state = bin2hex(random_bytes(16));
    $_SESSION['google_oauth_state'] = $state;

    return GOOGLE_AUTH_URL . '?' . http_build_query([
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'redirect_uri'  => env('GOOGLE_REDIRECT_URI'),
        'response_type' => 'code',
        'scope'         => 'openid email profile',
        'state'         => $state,
        'access_type'   => 'online',
        'prompt'        => 'select_account',
    ]);
}

// ── Intercambiar code por access_token ────────────────────────────────────────
function googleExchangeCode(string $code): ?array
{
    $ch = curl_init(GOOGLE_TOKEN_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'code'          => $code,
            'client_id'     => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect_uri'  => env('GOOGLE_REDIRECT_URI'),
            'grant_type'    => 'authorization_code',
        ]),
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) return null;
    $data = json_decode($response, true);
    return !empty($data['access_token']) ? $data : null;
}

// ── Obtener datos del usuario de Google ───────────────────────────────────────
function googleGetUser(string $accessToken): ?array
{
    $ch = curl_init(GOOGLE_USER_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER     => ["Authorization: Bearer $accessToken"],
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) return null;
    $data = json_decode($response, true);
    return !empty($data['email']) ? $data : null;
}

// ── Encontrar o crear usuario por Google ID ───────────────────────────────────
function googleFindOrCreateUser(array $googleUser): ?array
{
    $pdo = getDB();

    // Buscar por google_id primero
    $stmt = $pdo->prepare("SELECT * FROM ms_usuarios WHERE google_id = ? LIMIT 1");
    $stmt->execute([$googleUser['sub']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Actualizar avatar si cambió
        if (!empty($googleUser['picture']) && $user['avatar_url'] !== $googleUser['picture']) {
            $pdo->prepare("UPDATE ms_usuarios SET avatar_url = ? WHERE id = ?")
                ->execute([$googleUser['picture'], $user['id']]);
        }
        return $user;
    }

    // Buscar por email (usuario ya registrado con contraseña)
    $stmt = $pdo->prepare("SELECT * FROM ms_usuarios WHERE email = ? LIMIT 1");
    $stmt->execute([$googleUser['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Vincular cuenta existente con Google
        $pdo->prepare("
            UPDATE ms_usuarios
            SET google_id = ?, avatar_url = ?, auth_provider = 'google'
            WHERE id = ?
        ")->execute([$googleUser['sub'], $googleUser['picture'] ?? null, $user['id']]);
        $user['google_id']  = $googleUser['sub'];
        $user['avatar_url'] = $googleUser['picture'] ?? null;
        return $user;
    }

    // Crear nuevo usuario con cuenta de Google
    $nombre   = $googleUser['given_name']  ?? explode(' ', $googleUser['name'] ?? '')[0];
    $apellido = $googleUser['family_name'] ?? '';

    $stmt = $pdo->prepare("
        INSERT INTO ms_usuarios
            (tipo, email, password, nombre, apellido, google_id, avatar_url, auth_provider,
             estado, email_verificado)
        VALUES
            ('individual', ?, '', ?, ?, ?, ?, 'google', 'activo', 1)
    ");
    $stmt->execute([
        $googleUser['email'],
        $nombre,
        $apellido,
        $googleUser['sub'],
        $googleUser['picture'] ?? null,
    ]);

    $id = (int) $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT * FROM ms_usuarios WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
