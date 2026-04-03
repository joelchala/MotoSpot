<?php
/**
 * MotoSpot — Cloudinary Integration
 * CDN alternativo para imágenes. No reemplaza storage/uploads.
 * Uso: subir imágenes a Cloudinary y obtener URLs optimizadas.
 */

if (!defined('MOTOSPOT_CLOUDINARY')) {
    define('MOTOSPOT_CLOUDINARY', true);
}

// ── Configuración ─────────────────────────────────────────────────────────────

function cloudinaryConfig(): array
{
    return [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME', ''),
        'api_key'    => env('CLOUDINARY_API_KEY', ''),
        'api_secret' => env('CLOUDINARY_API_SECRET', ''),
        'base_url'   => env('CLOUDINARY_BASE_URL', ''),
    ];
}

// ── Upload ────────────────────────────────────────────────────────────────────

/**
 * Sube una imagen a Cloudinary.
 *
 * @param string $filePath   Ruta local del archivo
 * @param string $folder     Carpeta en Cloudinary (ej: 'motospot/vehiculos')
 * @param string $publicId   ID público opcional (sin extensión)
 * @return array ['ok' => bool, 'public_id' => string, 'url' => string, 'error' => string]
 */
function cloudinaryUpload(string $filePath, string $folder = 'motospot', string $publicId = ''): array
{
    $cfg = cloudinaryConfig();

    if (empty($cfg['cloud_name']) || empty($cfg['api_key']) || empty($cfg['api_secret'])) {
        return ['ok' => false, 'error' => 'Cloudinary no configurado'];
    }

    if (!file_exists($filePath)) {
        return ['ok' => false, 'error' => 'Archivo no encontrado'];
    }

    $timestamp = time();
    $params    = ['folder' => $folder, 'timestamp' => $timestamp];
    if ($publicId) $params['public_id'] = $publicId;

    ksort($params);
    $paramStr  = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    $signature = sha1($paramStr . $cfg['api_secret']);

    $postFields = array_merge($params, [
        'file'      => new CURLFile($filePath),
        'api_key'   => $cfg['api_key'],
        'signature' => $signature,
    ]);

    $url = "https://api.cloudinary.com/v1_1/{$cfg['cloud_name']}/image/upload";
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST            => true,
        CURLOPT_POSTFIELDS      => $postFields,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_TIMEOUT         => 30,
        CURLOPT_CONNECTTIMEOUT  => 8,
        CURLOPT_SSL_VERIFYPEER  => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['ok' => false, 'error' => 'cURL error: ' . $curlErr];
    }

    $data = json_decode($response, true);

    if ($httpCode !== 200 || empty($data['public_id'])) {
        $msg = $data['error']['message'] ?? $response;
        return ['ok' => false, 'error' => $msg];
    }

    return [
        'ok'        => true,
        'public_id' => $data['public_id'],
        'url'       => $data['secure_url'],
        'width'     => $data['width']  ?? 0,
        'height'    => $data['height'] ?? 0,
        'bytes'     => $data['bytes']  ?? 0,
    ];
}

/**
 * Sube una imagen desde una URL externa a Cloudinary (fetch).
 * Útil para importar imágenes de Unsplash/Pexels/Pixabay.
 */
function cloudinaryUploadUrl(string $imageUrl, string $folder = 'motospot', string $publicId = ''): array
{
    $cfg = cloudinaryConfig();

    if (empty($cfg['cloud_name']) || empty($cfg['api_key']) || empty($cfg['api_secret'])) {
        return ['ok' => false, 'error' => 'Cloudinary no configurado'];
    }

    $timestamp = time();
    $params    = ['folder' => $folder, 'timestamp' => $timestamp];
    if ($publicId) $params['public_id'] = $publicId;

    ksort($params);
    $paramStr  = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    $signature = sha1($paramStr . $cfg['api_secret']);

    $postData = array_merge($params, [
        'file'      => $imageUrl,
        'api_key'   => $cfg['api_key'],
        'signature' => $signature,
    ]);

    $url = "https://api.cloudinary.com/v1_1/{$cfg['cloud_name']}/image/upload";
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST            => true,
        CURLOPT_POSTFIELDS      => http_build_query($postData),
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_TIMEOUT         => 30,
        CURLOPT_CONNECTTIMEOUT  => 8,
        CURLOPT_SSL_VERIFYPEER  => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) return ['ok' => false, 'error' => $curlErr];

    $data = json_decode($response, true);

    if ($httpCode !== 200 || empty($data['public_id'])) {
        return ['ok' => false, 'error' => $data['error']['message'] ?? 'Error desconocido'];
    }

    return [
        'ok'        => true,
        'public_id' => $data['public_id'],
        'url'       => $data['secure_url'],
        'width'     => $data['width']  ?? 0,
        'height'    => $data['height'] ?? 0,
    ];
}

// ── Eliminar ──────────────────────────────────────────────────────────────────

/**
 * Elimina una imagen de Cloudinary por su public_id.
 */
function cloudinaryDelete(string $publicId): bool
{
    $cfg       = cloudinaryConfig();
    $timestamp = time();
    $signature = sha1("public_id={$publicId}&timestamp={$timestamp}" . $cfg['api_secret']);

    $url = "https://api.cloudinary.com/v1_1/{$cfg['cloud_name']}/image/destroy";
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'public_id' => $publicId,
            'timestamp' => $timestamp,
            'api_key'   => $cfg['api_key'],
            'signature' => $signature,
        ]),
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_TIMEOUT         => 15,
        CURLOPT_CONNECTTIMEOUT  => 8,
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    return ($data['result'] ?? '') === 'ok';
}

// ── URLs con transformaciones ─────────────────────────────────────────────────

/**
 * Genera URL de Cloudinary con transformaciones.
 *
 * @param string $publicId   public_id almacenado en BD
 * @param array  $opts       ['w' => 800, 'h' => 600, 'c' => 'fill', 'q' => 'auto', 'f' => 'auto']
 */
function cloudinaryUrl(string $publicId, array $opts = []): string
{
    $cfg = cloudinaryConfig();
    if (empty($cfg['cloud_name']) || empty($publicId)) return '';

    $transforms = [];
    if (!empty($opts['w']))  $transforms[] = 'w_' . (int)$opts['w'];
    if (!empty($opts['h']))  $transforms[] = 'h_' . (int)$opts['h'];
    if (!empty($opts['c']))  $transforms[] = 'c_' . $opts['c'];
    $transforms[] = 'q_' . ($opts['q'] ?? 'auto');
    $transforms[] = 'f_' . ($opts['f'] ?? 'auto');

    $t = implode(',', $transforms);
    return "https://res.cloudinary.com/{$cfg['cloud_name']}/image/upload/{$t}/{$publicId}";
}

/**
 * Presets de transformación comunes.
 */
function cloudinaryThumb(string $publicId): string
{
    return cloudinaryUrl($publicId, ['w' => 400, 'h' => 300, 'c' => 'fill']);
}

function cloudinaryCard(string $publicId): string
{
    return cloudinaryUrl($publicId, ['w' => 800, 'h' => 533, 'c' => 'fill']);
}

function cloudinaryFull(string $publicId): string
{
    return cloudinaryUrl($publicId, ['w' => 1280, 'h' => 853, 'c' => 'limit']);
}
