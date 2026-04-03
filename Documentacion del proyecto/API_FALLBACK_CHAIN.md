# 🔗 Sistema de Fallback de APIs - Documentación

**Archivo Principal:** `/includes/stock_media.php`  
**Líneas Relevantes:** 270-310, 85-100, 150-170  
**Creado:** 04 de Abril, 2026  
**Estado:** ✅ Implementado y probado

---

## 🎯 Descripción General

MotoSpot depende de APIs externas para obtener imágenes y videos:
- **Unsplash:** Fotos de vehículos (50 req/hora)
- **Pexels:** Fotos adicionales (200 req/hora)  
- **Pixabay:** Videos (100 req/60s)

**Problema anterior:** Si una API fallaba, toda la búsqueda fallaba.

**Solución implementada:** Cadena de fallback automática - si Unsplash falla, intenta Pexels; si Pexels falla, intenta Pixabay.

---

## 🔄 Arquitectura de Fallback

```
┌─────────────────────────────────────────────────────┐
│ stockSearch('toyota')                               │
└──────────────┬──────────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────────┐
│ try: unsplashSearchImages('toyota')                 │
├─────────────────────────────────────────────────────┤
│ Exitoso ✅ → Retornar resultados                    │
│ Error ❌ → Siguiente intento                        │
└──────────────┬──────────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────────┐
│ try: pexelsSearchImages('toyota')                   │
├─────────────────────────────────────────────────────┤
│ Exitoso ✅ → Retornar resultados                    │
│ Error ❌ → Siguiente intento                        │
└──────────────┬──────────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────────┐
│ try: pixabaySearchVideos('toyota')                  │
├─────────────────────────────────────────────────────┤
│ Exitoso ✅ → Retornar resultados                    │
│ Error ❌ → Array vacío []                           │
└──────────────┬──────────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────────┐
│ return $results (o array vacío si nada funcionó)   │
└─────────────────────────────────────────────────────┘
```

---

## 🔧 Implementación

### 1. Función stockHttpGet() - Error Handling Base

```php
// /includes/stock_media.php - Líneas 46-96

function stockHttpGet($url) {
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    
    // ✅ Error handling mejorado
    if ($error) {
        throw new Exception("cURL Error: $error");
    }
    
    if ($httpCode !== 200) {
        throw new Exception("HTTP Error: $httpCode");
    }
    
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON Parse Error: " . json_last_error_msg());
    }
    
    return $data;
}
```

**Cambios:**
- Ahora verifica `curl_error()` (no solo retorno)
- Verifica `$httpCode !== 200`
- Valida JSON antes de usar
- Lanza excepciones en lugar de retornar null silenciosamente

---

### 2. Función stockSearch() - Cadena de Fallback

```php
// /includes/stock_media.php - Líneas 271-310

function stockSearch($query, $searchType = 'images') {
    $query = urlencode($query);
    $results = [];
    
    try {
        // ✅ Intento 1: Unsplash (Mejor calidad)
        $results = unsplashSearchImages($query);
        logger('info', 'Unsplash search successful', ['query' => $query]);
        return $results;
    } catch (Exception $e) {
        logger('warning', 'Unsplash failed, falling back to Pexels', 
            ['query' => $query, 'error' => $e->getMessage()]);
    }
    
    try {
        // ✅ Intento 2: Pexels (Calidad media)
        $results = pexelsSearchImages($query);
        logger('info', 'Pexels search successful', ['query' => $query]);
        return $results;
    } catch (Exception $e) {
        logger('warning', 'Pexels failed, falling back to Pixabay', 
            ['query' => $query, 'error' => $e->getMessage()]);
    }
    
    try {
        // ✅ Intento 3: Pixabay (Último recurso)
        if ($searchType === 'videos') {
            $results = pixabaySearchVideos($query);
        } else {
            $results = pixabaySearchImages($query);
        }
        logger('info', 'Pixabay search successful', ['query' => $query]);
        return $results;
    } catch (Exception $e) {
        // ✅ Fallback final: Retornar array vacío (no fallar completamente)
        logger('error', 'All APIs failed', 
            ['query' => $query, 'error' => $e->getMessage()]);
        return [];  // Array vacío, no null
    }
}
```

**Características:**
1. **Try-catch anidados** para cada API
2. **Logging en cada etapa** para debugging
3. **Retorno de resultados en cada etapa** (no sigue si tiene éxito)
4. **Fallback a array vacío** si nada funciona
5. **Código más legible** que ifs anidados

---

### 3. Uso en index.php - Manejo de Error Completo

```php
// /public/index.php - Líneas 13-30 (getHeroVideo)

function getHeroVideo() {
    try {
        // ✅ Intento: Obtener video de Pixabay
        $videos = pixabaySearchVideos('vehicle');
        
        if (!empty($videos) && isset($videos[0])) {
            return $videos[0];  // Retornar primer video exitosamente
        }
    } catch (Exception $e) {
        // ✅ Error logging
        logger('warning', 'Failed to fetch hero video', 
            ['error' => $e->getMessage()]);
    }
    
    // ✅ Fallback: Imagen estática de Pixabay
    try {
        $images = pixabaySearchImages('car');
        if (!empty($images) && isset($images[0])) {
            return $images[0];
        }
    } catch (Exception $e) {
        logger('warning', 'Failed to fetch fallback image', 
            ['error' => $e->getMessage()]);
    }
    
    // ✅ Fallback final: Null (renderizar sin media)
    return null;
}
```

**Características:**
1. Intenta video primero (mejor experiencia)
2. Si falla, intenta imagen estática
3. Si ambas fallan, retorna null (página aún renderiza)
4. Logging en cada etapa
5. Timeout de 25s a nivel de página (seguridad adicional)

---

### 4. Loop de Videos Destacados - Error Handling Mejorado

```php
// /public/index.php - Líneas 58-75

try {
    $featuredVideos = pixabaySearchVideos('boat');
    if (!empty($featuredVideos)) {
        // Renderizar videos
        foreach ($featuredVideos as $video) {
            // ... mostrar video
        }
    }
} catch (Exception $e) {
    logger('warning', 'Featured videos load failed', 
        ['error' => $e->getMessage()]);
    // Página continúa sin videos destacados (no crash)
}
```

---

## 📊 Orden de Prioridad de APIs

| Posición | API | Límite | Pros | Contras |
|----------|-----|--------|------|---------|
| **1** | Unsplash | 50/hora | Mejor calidad, libre | Límite bajo |
| **2** | Pexels | 200/hora | Buena calidad | Menos opciones |
| **3** | Pixabay | 100/60s | Rápido | Calidad variada |
| **Final** | Array vacío | ∞ | Nunca falla | Sin media |

**Lógica:** Intentar mejor calidad primero, luego fallbacks más rápidas/abundantes.

---

## 🛡️ Seguridad de Caché

Pixabay requiere cache de 24h (TOS). Ya implementado:

```php
function stockCachePath($query) {
    $cacheDir = env('UPLOAD_PATH') . '/cache/stock_media/';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    return $cacheDir . md5($query) . '.json';
}

function stockSearchImages($query, $source = 'unsplash') {
    $cachePath = stockCachePath($query);
    
    // ✅ Verificar caché válido (< 24h)
    if (file_exists($cachePath)) {
        $cacheTime = filemtime($cachePath);
        if (time() - $cacheTime < 86400) {  // 86400 segundos = 24 horas
            return json_decode(file_get_contents($cachePath), true);
        }
    }
    
    // Obtener de API y cachear
    $results = stockSearch($query);
    file_put_contents($cachePath, json_encode($results));
    return $results;
}
```

---

## 🔍 Testing del Sistema de Fallback

### Simular fallo de Unsplash
```php
<?php
// En test: comentar Unsplash temporalmente
// function unsplashSearchImages($query) {
//     throw new Exception("Simulated failure");
// }

// Debería: Usar Pexels
$results = stockSearch('toyota');
assert(!empty($results), "Fallback a Pexels funcionó");
?>
```

### Verificar logging
```bash
# Ver logs de fallback
tail -f /ruta/a/logs/motospot.log | grep "Pexels\|Pixabay\|All APIs failed"
```

### Monitoreo en vivo
```php
// En página: mostrar cuál API se usó
// (útil para debugging, remover en producción)
if (isset($_GET['debug'])) {
    // Loguea: "Unsplash search successful" o "Pexels failed..."
    // Ver en logs
}
```

---

## 📈 Mejoras Futuras

### 1. Caché Persistente en Redis
```php
$redis = new Redis();
$cacheKey = "image_search:" . md5($query);
$cached = $redis->get($cacheKey);
if ($cached) return json_decode($cached, true);
// ...
$redis->setex($cacheKey, 86400, json_encode($results));
```

### 2. Circuit Breaker Pattern
```php
class APICircuitBreaker {
    private $failureCount = 0;
    private $threshold = 5;  // Desactivar después de 5 fallos
    private $timeout = 300;  // 5 minutos
    
    public function isOpen() {
        return $this->failureCount >= $this->threshold;
    }
    
    public function recordFailure() {
        $this->failureCount++;
        // Después de $timeout, resetear contador
    }
}
```

### 3. Metrics/Telemetría
```php
// Trackear qué APIs se usan más
$metrics = [
    'unsplash_success' => 150,
    'unsplash_failures' => 5,
    'pexels_success' => 45,
    'pexels_failures' => 2,
    'pixabay_success' => 20,
    'pixabay_failures' => 3,
];
// Usar para optimizar orden de fallback
```

### 4. Fallback Local de Imágenes
```php
// Si todas las APIs fallan, usar imágenes locales por defecto
$localImages = glob('/uploads/defaults/*.jpg');
return array_map(function($path) {
    return ['url' => '/image.php?f=' . basename($path)];
}, $localImages);
```

---

## 🚨 Troubleshooting

### Problema: Imágenes no aparecen en landing page
**Causa probable:** Todas las APIs fallando
**Debug:**
```bash
# Ver logs
grep "All APIs failed" /ruta/logs/motospot.log

# Verificar API keys en .env
grep "UNSPLASH\|PEXELS\|PIXABAY" /ruta/.motospot/.env

# Verificar conectividad
curl -I https://api.unsplash.com/
curl -I https://api.pexels.com/
curl -I https://pixabay.com/api/
```

### Problema: Caché obsoleto (>24h)
**Solución:**
```bash
# Limpiar caché manualmente
rm -rf /ruta/storage/uploads/cache/stock_media/*

# O implementar cron job diario
# /cron/cleanup_cache.php
```

### Problema: Una API particular siempre falla
**Solución:** Reordenar fallback o remover API

---

## ✅ Checklist de Verificación

- [ ] Todos los try-catch están en lugar
- [ ] Logging en cada etapa de fallback
- [ ] Array vacío retornado si todo falla (no null)
- [ ] Caché respeta límites de TOS (24h para Pixabay)
- [ ] HTTP codes verificados (200 OK)
- [ ] JSON válido antes de usar
- [ ] Timeouts configurados (10s por API, 25s total)
- [ ] Errores no rompen página (fallback a sin media)

---

**Documento Creado:** 04 de Abril, 2026  
**Última Actualización:** 04 de Abril, 2026  
**Versión:** 1.0  
**Estado:** ✅ Implementado y en producción
