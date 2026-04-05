# REPORTE DE RENDIMIENTO — MotoSpot

**Fecha:** 2026-04-04  
**Stack:** PHP 8.3, MySQL (InnoDB), cURL para APIs externas  
**Entorno:** Hostinger hosting compartido

---

## RESUMEN

| Categoría | Issues | Impacto |
|-----------|--------|---------|
| Consultas SQL | 6 | Alto |
| APIs Externas | 4 | Alto |
| Índices BD | 3 | Medio |
| Caching | 4 | Medio |
| Assets Frontend | 5 | Medio |
| **TOTAL** | **22** | — |

---

## CONSULTAS SQL

### PERF-01: Subqueries Correlacionadas por Fila [ALTO]
**Archivos:** `listado-vehiculos.php:99`, `detalle-vehiculo.php:58-67`, `embarcaciones.php:43`, `mis-publicaciones.php:49`

```sql
-- ACTUAL: N subqueries (una por cada vehículo)
SELECT v.*, 
    (SELECT url_foto FROM ms_vehiculo_fotos WHERE vehiculo_id = v.id AND es_principal = 1 LIMIT 1) as foto_principal
FROM ms_vehiculos v ...

-- OPTIMIZADO: 1 JOIN
SELECT v.*, vf.url_foto as foto_principal
FROM ms_vehiculos v
LEFT JOIN ms_vehiculo_fotos vf ON vf.vehiculo_id = v.id AND vf.es_principal = 1
```

**Impacto:** Con 100 vehículos, se ejecutan 101 consultas en lugar de 1.

---

### PERF-02: SELECT * sin Especificar Columnas [MEDIO]
**Archivos:** Múltiples (auth.php, auth-google-token.php, detalle-vehiculo.php)

`SELECT * FROM ms_usuarios` trae todas las columnas incluyendo `password`, `google_id`, `avatar_url`, etc. Solo se necesitan 5-8 columnas.

**Optimización:** Especificar solo las columnas necesarias:
```sql
SELECT id, nombre, apellido, email, telefono, tipo, rol, estado, plan, ...
```

---

### PERF-03: Incremento de Vistas sin Rate Limiting [ALTO]
**Archivo:** `detalle-vehiculo.php:45`

```sql
UPDATE ms_vehiculos SET vistas = vistas + 1 WHERE id = ?
```

Se ejecuta en CADA carga de página. Un bot puede generar miles de UPDATEs.

**Optimización:**
```php
// Solo incrementar si no se ha visto en esta sesión
if (!isset($_SESSION['viewed_' . $vehiculoId])) {
    executeQuery("UPDATE ms_vehiculos SET vistas = vistas + 1 WHERE id = ?", [$vehiculoId]);
    $_SESSION['viewed_' . $vehiculoId] = true;
}
```

---

### PERF-04: COUNT(*) Separado en Listado [MEDIO]
**Archivos:** `listado-vehiculos.php:93-94`, `embarcaciones.php:39`

Se ejecuta una query de COUNT y luego otra de SELECT con los mismos WHERE. Se puede combinar con `SQL_CALC_FOUND_ROWS` o hacer un solo roundtrip.

---

### PERF-05: Consultas de Filtros sin Índices Compuestos [MEDIO]
**Archivos:** `listado-vehiculos.php`, `embarcaciones.php`

Las consultas filtran por combinaciones de: `estado_publicacion`, `tipo_vehiculo`, `marca`, `ciudad`, `precio`, `ano`, `transmision`, `combustible`. Solo hay índices básicos.

**Índices recomendados:**
```sql
-- Índices compuestos para consultas frecuentes
CREATE INDEX idx_estado_tipo ON ms_vehiculos (estado_publicacion, tipo_vehiculo);
CREATE INDEX idx_estado_marca ON ms_vehiculos (estado_publicacion, marca);
CREATE INDEX idx_estado_ciudad ON ms_vehiculos (estado_publicacion, ciudad);
CREATE INDEX idx_estado_precio ON ms_vehiculos (estado_publicacion, precio);
CREATE INDEX idx_estado_destacado_fecha ON ms_vehiculos (estado_publicacion, destacado, fecha_publicacion);
CREATE INDEX idx_vehiculo_principal ON ms_vehiculo_fotos (vehiculo_id, es_principal);
```

---

### PERF-06: Consulta de Marcas/Ciudades sin Cache [MEDIO]
**Archivo:** `listado-vehiculos.php:109-110`

```php
$marcas = fetchAll("SELECT DISTINCT marca FROM ms_vehiculos WHERE estado_publicacion = 'activo' ORDER BY marca");
$ciudades = fetchAll("SELECT DISTINCT ciudad FROM ms_vehiculos WHERE estado_publicacion = 'activo' ORDER BY ciudad");
```

Se ejecutan en cada carga del listado. Estos datos cambian raramente.

**Optimización:** Cachear en sesión o archivo por 1 hora.

---

## APIs EXTERNAS

### PERF-07: 3-6 Llamadas Bloqueantes a APIs de Video en Index [ALTO]
**Archivo:** `public/index.php`

```php
// 4 llamadas secuenciales a Pixabay (hero + 3 búsquedas de videos)
$hero = getHeroVideo();          // ~2-8s si Pixabay responde lento
foreach (['sports car racing', 'motorcycle ride', 'luxury automobile'] as $vq) {
    $videos = pixabaySearchVideos($vq, 3);  // 3 llamadas × ~2-8s = 6-24s
}
```

**Tiempo potencial:** 8-32 segundos si las APIs responden lento. El usuario ve un spinner.

**Optimización:**
1. Pre-cachear videos con cron job (cada 6h)
2. Cargar videos vía AJAX después de que el hero renderice
3. Usar `set_time_limit(25)` (ya implementado) como protección, pero no como solución

---

### PERF-08: Stock Search con Fallback Secuencial [MEDIO]
**Archivo:** `includes/stock_media.php:stockSearch()`

Las 3 APIs se llaman secuencialmente. Si Unsplash falla (timeout 8s), luego intenta Pexels (8s), luego Pixabay (8s). Total posible: 24s.

**Optimización:** Usar `curl_multi` para llamadas paralelas.

---

### PERF-09: Cache de Stock Media Sin Invalidación [MEDIO]
**Archivo:** `includes/stock_media.php:22-42`

Cache de 24h sin mecanismo de invalidación manual. Si una API cambia formato, el cache obsoleto persiste 24h.

---

### PERF-10: Timeouts de cURL Demasiado Altos [MEDIO]
**Archivos:** Múltiples

```php
CURLOPT_TIMEOUT => 30,        // cloudinary.php
CURLOPT_TIMEOUT => 15,        // google_oauth.php
CURLOPT_TIMEOUT => 8,         // stock_media.php (ok)
```

30 segundos de timeout en Cloudinary es excesivo para un upload interactivo.

**Optimización:** Reducir a 15s para uploads, 8s para búsquedas.

---

## CACHING

### PERF-11: Sin Cache de Páginas [ALTO]
Ninguna página tiene cache HTTP. El header `Cache-Control` solo está en `image.php`.

**Optimización:**
```php
// Para listados (cachear 5 minutos)
header('Cache-Control: public, max-age=300');

// Para assets estáticos (cachear 1 año)
header('Cache-Control: public, max-age=31536000, immutable');
```

---

### PERF-12: Sin OPcache Verificado [MEDIO]
PHP OPcache debería estar habilitado en producción. No hay verificación en health check.

---

### PERF-13: Config Cargada en Cada Request [MEDIO]
`config.php` se carga y parsea en cada request. Con OPcache esto es mitigado, pero el array de configuración se crea en cada request.

---

### PERF-14: Consultas de Estadísticas de Perfil sin Cache [MEDIO]
**Archivo:** `perfil.php:105-118`

3 queries de COUNT/SUM se ejecutan en cada carga de perfil.

---

## FRONTEND

### PERF-15: Scripts Render-Blocking [MEDIO]
**Archivo:** `public/index.php`

```html
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>  <!-- Blocking -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> <!-- Blocking -->
```

**Optimización:** Agregar `defer` o `async` al script, usar `preload` para CSS crítico.

---

### PERF-16: CSS Inline Masivo [MEDIO]
**Archivos:** `planes.php` (~100 líneas CSS), `admin-codigos.php` (~80 líneas), `recuperar-password.php` (~50 líneas), `reset-password.php` (~50 líneas)

CSS inline no es cacheable por el navegador.

**Optimización:** Mover a archivos CSS externos.

---

### PERF-17: JS Inline en Index [MEDIO]
**Archivo:** `public/index.php` (~100 líneas JS)

No es cacheable.

---

### PERF-18: Sin Lazy Loading en Imágenes del Hero [BAJO]
Las imágenes de categorías cargan inmediatamente.

---

### PERF-19: Font Awesome Completo (~60KB) [MEDIO]
Se carga Font Awesome completo cuando solo se usan ~20 iconos.

**Optimización:** Usar Font Awesome subset o SVG icons.

---

### PERF-20: Sin Compresión GZIP Configurada [MEDIO]
`gzip_compression => true` en config pero no se aplica. Falta `.htaccess`:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css application/javascript
</IfModule>
```

---

## BASE DE DATOS

### PERF-21: Tabla de Emails Sin Limpieza [MEDIO]
`ms_email_queue` crece indefinidamente. Los emails sent/failed se acumulan.

**Optimización:** Cron para eliminar emails mayores a 30 días.

---

### PERF-22: Sin Connection Pooling [BAJO]
Cada request crea una nueva conexión PDO. En hosting compartido esto es normal, pero para escalabilidad futura considerar pooling.

---

## RECOMENDACIONES DE OPTIMIZACIÓN

### Inmediato (esta semana):
1. Agregar índices compuestos a `ms_vehiculos` y `ms_vehiculo_fotos`
2. Reemplazar subqueries por LEFT JOINs
3. Agregar rate limiting al incremento de vistas
4. Configurar GZIP en `.htaccess`

### Corto plazo (este mes):
5. Pre-cachear videos de Pixabay con cron job
6. Implementar cache HTTP para listados
7. Mover CSS inline a archivos externos
8. Usar `curl_multi` para llamadas paralelas de stock media

### Medio plazo (próximo trimestre):
9. Implementar Redis para cache de consultas frecuentes
10. Implementar CDN (CloudFlare) para assets estáticos
11. Optimizar Font Awesome (subset o reemplazar con SVG)
12. Implementar connection pooling

---

## BENCHMARKS ESTIMADOS

| Página | Requests BD Actual | Requests BD Optimizado | Tiempo Estimado |
|--------|-------------------|----------------------|-----------------|
| Index | 4 APIs + 0 BD | 0 APIs (cache) + 0 BD | 0.5s vs 8-30s |
| Listado (12 items) | 14 queries | 2 queries | 0.1s vs 0.5s |
| Detalle vehículo | 4 queries | 2 queries | 0.05s vs 0.1s |
| Perfil | 5 queries | 2 queries | 0.05s vs 0.15s |

---

*Documento generado 2026-04-04.*
