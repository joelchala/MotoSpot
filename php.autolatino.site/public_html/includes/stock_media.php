<?php
/**
 * MotoSpot — Stock Media Integration
 * Wrapper unificado para Unsplash, Pexels y Pixabay.
 *
 * - Unsplash : 50 req/hora
 * - Pexels   : 200 req/hora
 * - Pixabay  : 100 req/60s — cache 24h OBLIGATORIO por TOS
 */

// Cargar variables de entorno (requerido para env())
require_once __DIR__ . '/env.php';

if (!defined('MOTOSPOT_STOCK_MEDIA')) {
    define('MOTOSPOT_STOCK_MEDIA', true);
}

const STOCK_CACHE_TTL = 86400; // 24 horas (requerido por Pixabay TOS)

// ── Cache en archivo ──────────────────────────────────────────────────────────

function stockCachePath(string $key): string
{
    $logPath = env('LOG_PATH', sys_get_temp_dir());
    $cacheDir = dirname($logPath) . '/cache';
    if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
    return $cacheDir . '/' . md5($key) . '.json';
}

function stockCacheGet(string $key): ?array
{
    $path = stockCachePath($key);
    if (!file_exists($path)) return null;
    if (time() - filemtime($path) > STOCK_CACHE_TTL) return null;
    $data = json_decode(file_get_contents($path), true);
    return $data ?: null;
}

function stockCacheSet(string $key, array $data): void
{
    file_put_contents(stockCachePath($key), json_encode($data));
}

// ── HTTP helper ───────────────────────────────────────────────────────────────

function stockHttpGet(string $url, array $headers = []): ?array
{
    try {
        $ch = curl_init($url);
        if (!$ch) {
            throw new Exception('Failed to initialize curl');
        }
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_TIMEOUT         => 8,
            CURLOPT_CONNECTTIMEOUT  => 5,
            CURLOPT_SSL_VERIFYPEER  => true,
            CURLOPT_HTTPHEADER      => $headers,
            CURLOPT_USERAGENT       => 'MotoSpot/1.0',
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception('Curl error: ' . $curlError);
        }
        
        if ($httpCode !== 200) {
            logger('warning', 'API returned non-200 status', ['url' => $url, 'status' => $httpCode]);
            return null;
        }
        
        if (!$response) {
            throw new Exception('Empty response from API');
        }
        
        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response: ' . json_last_error_msg());
        }
        
        return $decoded;
    } catch (Exception $e) {
        logger('error', 'API request failed', ['url' => $url, 'error' => $e->getMessage()]);
        return null;
    }
}

// ── Unsplash ──────────────────────────────────────────────────────────────────

/**
 * Busca imágenes en Unsplash.
 *
 * @param string $query    Término de búsqueda (ej: 'motorcycle', 'sedan car')
 * @param int    $perPage  Resultados por página (max 30)
 * @param int    $page     Página
 * @return array Lista de imágenes normalizadas
 */
function unsplashSearch(string $query, int $perPage = 10, int $page = 1): array
{
    $cacheKey = "unsplash_{$query}_{$page}_{$perPage}";
    $cached   = stockCacheGet($cacheKey);
    if ($cached !== null) return $cached;

    $key = env('UNSPLASH_ACCESS_KEY', '');
    if (empty($key)) return [];

    $url  = 'https://api.unsplash.com/search/photos?' . http_build_query([
        'query'    => $query,
        'per_page' => $perPage,
        'page'     => $page,
    ]);

    $data = stockHttpGet($url, ["Authorization: Client-ID $key"]);
    if (empty($data['results'])) return [];

    $results = array_map(fn($img) => [
        'source'       => 'unsplash',
        'id'           => $img['id'],
        'url_thumb'    => $img['urls']['small'],
        'url_regular'  => $img['urls']['regular'],
        'url_full'     => $img['urls']['full'],
        'url_download' => $img['links']['download_location'], // usar para descarga (trackeo)
        'alt'          => $img['alt_description'] ?? $query,
        'author'       => $img['user']['name'] ?? '',
        'author_url'   => $img['user']['links']['html'] ?? '',
        'width'        => $img['width'],
        'height'       => $img['height'],
    ], $data['results']);

    stockCacheSet($cacheKey, $results);
    return $results;
}

/**
 * Registra descarga en Unsplash (requerido por sus TOS).
 */
function unsplashTrackDownload(string $downloadLocation): void
{
    $key = env('UNSPLASH_ACCESS_KEY', '');
    if (empty($key) || empty($downloadLocation)) return;
    stockHttpGet($downloadLocation, ["Authorization: Client-ID $key"]);
}

// ── Pexels ────────────────────────────────────────────────────────────────────

/**
 * Busca imágenes en Pexels.
 *
 * @param string $query
 * @param int    $perPage  Max 80
 * @param int    $page
 */
function pexelsSearch(string $query, int $perPage = 10, int $page = 1): array
{
    $cacheKey = "pexels_{$query}_{$page}_{$perPage}";
    $cached   = stockCacheGet($cacheKey);
    if ($cached !== null) return $cached;

    $key = env('PEXELS_API_KEY', '');
    if (empty($key)) return [];

    $url  = 'https://api.pexels.com/v1/search?' . http_build_query([
        'query'    => $query,
        'per_page' => $perPage,
        'page'     => $page,
    ]);

    $data = stockHttpGet($url, ["Authorization: $key"]);
    if (empty($data['photos'])) return [];

    $results = array_map(fn($img) => [
        'source'      => 'pexels',
        'id'          => $img['id'],
        'url_thumb'   => $img['src']['medium'],
        'url_regular' => $img['src']['large'],
        'url_full'    => $img['src']['original'],
        'url_download'=> $img['src']['original'],
        'alt'         => $img['alt'] ?? $query,
        'author'      => $img['photographer'] ?? '',
        'author_url'  => $img['photographer_url'] ?? '',
        'width'       => $img['width'],
        'height'      => $img['height'],
    ], $data['photos']);

    stockCacheSet($cacheKey, $results);
    return $results;
}

// ── Pixabay ───────────────────────────────────────────────────────────────────

/**
 * Busca imágenes en Pixabay.
 * Cache de 24h OBLIGATORIO por TOS. No hotlinking permanente.
 *
 * @param string $query
 * @param int    $perPage  Max 200
 * @param int    $page
 */
function pixabaySearchImages(string $query, int $perPage = 10, int $page = 1): array
{
    $cacheKey = "pixabay_img_{$query}_{$page}_{$perPage}";
    $cached   = stockCacheGet($cacheKey);
    if ($cached !== null) return $cached;

    $key = env('PIXABAY_API_KEY', '');
    if (empty($key)) return [];

    $url = 'https://pixabay.com/api/?' . http_build_query([
        'key'           => $key,
        'q'             => urlencode($query),
        'image_type'    => 'photo',
        'per_page'      => $perPage,
        'page'          => $page,
        'safesearch'    => 'true',
        'lang'          => 'es',
    ]);

    $data = stockHttpGet($url);
    if (empty($data['hits'])) return [];

    $results = array_map(fn($img) => [
        'source'      => 'pixabay',
        'id'          => $img['id'],
        'url_thumb'   => $img['previewURL'],
        'url_regular' => $img['webformatURL'],
        'url_full'    => $img['largeImageURL'],
        'url_download'=> $img['largeImageURL'],
        'alt'         => $img['tags'] ?? $query,
        'author'      => $img['user'] ?? '',
        'author_url'  => 'https://pixabay.com/users/' . ($img['user'] ?? ''),
        'width'       => $img['imageWidth'],
        'height'      => $img['imageHeight'],
    ], $data['hits']);

    stockCacheSet($cacheKey, $results);
    return $results;
}

/**
 * Busca videos en Pixabay (para sección Canal de Videos).
 *
 * @param string $query
 * @param int    $perPage
 * @param int    $page
 */
function pixabaySearchVideos(string $query, int $perPage = 10, int $page = 1): array
{
    $cacheKey = "pixabay_vid_{$query}_{$page}_{$perPage}";
    $cached   = stockCacheGet($cacheKey);
    if ($cached !== null) return $cached;

    $key = env('PIXABAY_API_KEY', '');
    if (empty($key)) return [];

    $url = 'https://pixabay.com/api/videos/?' . http_build_query([
        'key'      => $key,
        'q'        => urlencode($query),
        'per_page' => $perPage,
        'page'     => $page,
        'lang'     => 'es',
    ]);

    $data = stockHttpGet($url);
    if (empty($data['hits'])) return [];

    $results = array_map(fn($vid) => [
        'source'    => 'pixabay',
        'id'        => $vid['id'],
        'url_thumb' => $vid['videos']['medium']['thumbnail'] ?? '',
        'url_medium'=> $vid['videos']['medium']['url'] ?? '',
        'url_large' => $vid['videos']['large']['url']  ?? $vid['videos']['medium']['url'] ?? '',
        'duration'  => $vid['duration'] ?? 0,
        'tags'      => $vid['tags'] ?? '',
        'author'    => $vid['user'] ?? '',
        'author_url'=> 'https://pixabay.com/users/' . ($vid['user'] ?? ''),
        'width'     => $vid['videos']['medium']['width']  ?? 0,
        'height'    => $vid['videos']['medium']['height'] ?? 0,
    ], $data['hits']);

    stockCacheSet($cacheKey, $results);
    return $results;
}

// ── Búsqueda unificada ────────────────────────────────────────────────────────

/**
 * Busca en todas las fuentes y combina resultados.
 * Orden: Unsplash → Pexels → Pixabay (fallback automático).
 *
 * @param string $query
 * @param int    $limit  Total de resultados deseados
 * @param array  $sources ['unsplash', 'pexels', 'pixabay'] — cuáles usar
 */
function stockSearch(string $query, int $limit = 12, array $sources = ['unsplash', 'pexels', 'pixabay']): array
{
    $results  = [];
    $perSource = (int) ceil($limit / count($sources));

    // Intenta cada fuente, con fallback a las siguientes si una falla
    if (in_array('unsplash', $sources)) {
        try {
            $unsplashResults = unsplashSearch($query, $perSource);
            $results = array_merge($results, $unsplashResults);
        } catch (Exception $e) {
            logger('warning', 'Unsplash API failed', ['query' => $query, 'error' => $e->getMessage()]);
        }
    }
    
    if (in_array('pexels', $sources)) {
        try {
            $pexelsResults = pexelsSearch($query, $perSource);
            $results = array_merge($results, $pexelsResults);
        } catch (Exception $e) {
            logger('warning', 'Pexels API failed', ['query' => $query, 'error' => $e->getMessage()]);
        }
    }
    
    if (in_array('pixabay', $sources)) {
        try {
            $pixabayResults = pixabaySearchImages($query, $perSource);
            $results = array_merge($results, $pixabayResults);
        } catch (Exception $e) {
            logger('warning', 'Pixabay API failed', ['query' => $query, 'error' => $e->getMessage()]);
        }
    }

    return array_slice($results, 0, $limit);
}

// ── Descarga y guarda en storage ─────────────────────────────────────────────

/**
 * Descarga una imagen de stock al storage local.
 * Requerido por Pixabay TOS (no hotlinking permanente).
 *
 * @param string $imageUrl  URL de la imagen
 * @param string $filename  Nombre del archivo (sin extensión)
 * @return string|null Ruta local del archivo guardado, o null si falla
 */
function stockDownloadToStorage(string $imageUrl, string $filename): ?string
{
    $uploadPath = rtrim(env('UPLOAD_PATH', ''), '/') . '/stock/';
    if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);

    $ext      = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
    $ext      = in_array(strtolower($ext), ['jpg','jpeg','png','webp']) ? strtolower($ext) : 'jpg';
    $fullPath = $uploadPath . $filename . '.' . $ext;

    if (file_exists($fullPath)) return $fullPath; // ya descargado

    $ch = curl_init($imageUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT      => 'MotoSpot/1.0',
    ]);
    $imageData = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$imageData) return null;

    file_put_contents($fullPath, $imageData);
    return $fullPath;
}
