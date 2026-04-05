# REPORTE DE SEGURIDAD — MotoSpot

**Fecha:** 2026-04-04  
**Stack:** PHP 8.3, MySQL (InnoDB), Google OAuth 2.0, Cloudinary  
**Estándares:** OWASP Top 10 2021, CWE

---

## RESUMEN

| Categoría | Críticos | Altos | Medios | Bajos |
|-----------|----------|-------|--------|-------|
| Autenticación | 2 | 4 | 3 | 1 |
| Autorización | 2 | 1 | 1 | 0 |
| CSRF | 1 | 3 | 0 | 0 |
| XSS | 0 | 2 | 1 | 0 |
| Inyección | 1 | 0 | 1 | 0 |
| Configuración | 3 | 2 | 2 | 1 |
| Upload | 0 | 1 | 1 | 0 |
| Session Management | 0 | 1 | 2 | 0 |
| **TOTAL** | **9** | **14** | **11** | **2** |

---

## CRÍTICOS

### SEC-01: Credenciales de BD Hardcodeadas [CWE-798]
**Archivo:** `includes/config.php:22-24`  
Las credenciales de MySQL están en texto plano en el código fuente. Cualquier persona con acceso al repositorio o a un backup del código tiene acceso completo a la BD.

**Remediation:**
1. Cambiar la contraseña de la BD inmediatamente
2. Eliminar credenciales de `config.php`
3. Usar únicamente `.env` vía `db.php`
4. Verificar que `.gitignore` excluya `config.php` si mantiene datos sensibles

---

### SEC-02: Token de Health Check Hardcodeado [CWE-798]
**Archivo:** `public/health.php:14`  
Token `ms_check_2026` expuesto en código. El health check revela versión PHP, versión MySQL, rutas del servidor y estado de extensiones.

**Remediation:**
```php
$token = $_GET['token'] ?? '';
if (!hash_equals(env('HEALTH_CHECK_TOKEN', ''), $token)) {
    http_response_code(403);
    die(json_encode(['status' => 'forbidden']));
}
```

---

### SEC-03: Funciones Críticas No Ejecutan — Código PHP Después de `?>` [CWE-480]
**Archivo:** `includes/functions.php`  
Múltiples cierres `?>` intermedios hacen que `validarURL()`, `validarPasswordSegura()`, `generarNombreArchivoSeguro()` y polyfills queden fuera del bloque PHP.

**Impacto:** Las funciones de validación de seguridad NO están disponibles. Si se llama, produce Fatal Error o se imprime como HTML.

**Remediation:** Eliminar TODOS los `?>` intermedios. Archivo terminado sin `?>`.

---

### SEC-04: Sin CSRF en mis-publicaciones.php [CWE-352]
**Archivo:** `public/mis-publicaciones.php:18-43`  
Formulario POST para pausar/activar publicaciones sin token CSRF.

**Remediation:**
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verificarCSRFToken($csrf_token)) {
        header('Location: /mis-publicaciones.php');
        exit();
    }
    // ... procesar acción
}
```

---

### SEC-05: Sin CSRF en recuperar-password.php [CWE-352]
**Archivo:** `public/recuperar-password.php:21`  
Permite solicitar reset de contraseña sin protección CSRF.

**Remediation:** Agregar `<input type="hidden" name="csrf_token" value="<?= generarCSRFToken() ?>">` y verificar en POST.

---

### SEC-06: Session Incompleta en OAuth — Pérdida de Privilegios Admin [CWE-287]
**Archivos:** `public/auth-google-token.php:126-131`, `public/oauth-google.php:62-66`  
OAuth establece 4-5 variables de sesión manualmente, omitiendo `usuario_rol`. Un admin que se autentique con Google pierde acceso admin.

**Remediation:** Usar `crearSesionUsuario($usuario)` en ambos endpoints.

---

### SEC-07: LIMIT/OFFSET Concatenados en SQL [CWE-89]
**Archivos:** `public/listado-vehiculos.php:104`, `public/embarcaciones.php:48`  
```php
$sql = "SELECT ... LIMIT $perPage OFFSET $offset";
```
Aunque `$perPage` y `$offset` son `intval()`, la concatenación directa es mala práctica.

**Remediation:** Usar `LIMIT :perPage OFFSET :offset` con bindParam.

---

### SEC-08: Reset Password No Ejecuta Correctamente [CWE-480]
**Archivo:** `public/reset-password.php:50-92`  
Falta llave de cierre del bloque POST. Las líneas de include de header/footer están DENTRO del if POST, haciendo que la página no renderice en GET.

**Remediation:** Agregar `}` para cerrar el bloque POST antes de las líneas 93+.

---

### SEC-09: TLS No Verificado en SMTP [CWE-297]
**Archivo:** `cron/process_emails.php:150`  
`stream_socket_enable_crypto()` puede retornar false sin que se verifique.

**Remediation:**
```php
$result = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
if ($result === false) {
    throw new Exception('TLS handshake failed');
}
```

---

## ALTOS

### SEC-10: Open Redirect en oauth-google.php [CWE-601]
**Archivo:** `public/oauth-google.php:76-79`  
`$_SESSION['oauth_redirect']` sin validación. Puede redirigir a sitios externos.

### SEC-11: Política de Contraseñas Inconsistente [CWE-521]
**Archivo:** `public/perfil.php:88`  
Acepta 6 caracteres sin complejidad. Registro exige 8+ con complejidad.

### SEC-12: User Enumeration por Timing [CWE-208]
**Archivo:** `public/recuperar-password.php`  
Timing difference entre email existente vs no existente.

### SEC-13: Rate Limiting Ausente [CWE-307]
**Archivos:** `login.php`, `recuperar-password.php`, `planes.php`  
Sin límite de intentos en ningún endpoint.

### SEC-14: Headers de Seguridad Ausentes [CWE-693]
Faltan: CSP, X-Content-Type-Options, X-Frame-Options, HSTS, Referrer-Policy.

### SEC-15: Upload Sin Verificación de Contenido [CWE-434]
**Archivo:** `public/publicar-vehiculo.php:177-187`  
No se usa `getimagesize()` para verificar contenido real.

### SEC-16: Token CSRF de Sesión Única [CWE-352]
Mismo token para todas las pestañas/solicitudes. No se regenera tras uso.

### SEC-17: image.php Sin Control de Acceso [CWE-552]
Cualquiera puede acceder a cualquier imagen conociendo la ruta.

### SEC-18: User Enumeration en Login [CWE-209]
Mensajes distintos para "email no registrado" vs "contraseña incorrecta".

### SEC-19: Error Message Expuesto al Usuario [CWE-209]
**Archivo:** `public/publicar-vehiculo.php:229`  
`$e->getMessage()` expuesto directamente.

### SEC-20: Session Cookies No Configuradas [CWE-614]
`session_start()` sin configurar `secure`, `httponly`, `samesite`.

### SEC-21: Logout por GET [CWE-352]
Accesible via GET directa. Atacante puede forzar logout.

### SEC-22: Session ID No Regenerado en Login [CWE-384]
`crearSesionUsuario()` no llama `regenerarSesion()`. Session fixation.

### SEC-23: Session Timeout No Implementado [CWE-613]
`session_lifetime => 7200` en config pero nunca verificado.

---

## MEDIOS

### SEC-24: Generación de Códigos No Criptográfica
`str_shuffle()` en lugar de `random_bytes()`.

### SEC-25: SELECT * Expone password Hash
`SELECT * FROM ms_usuarios` trae hash innecesariamente.

### SEC-26: Email Template Sin Sanitización de Contenido
`$contenido_safe = $contenido;` sin sanitización real.

### SEC-27: Logger Escribe Datos Sensibles
Tokens y contraseñas pueden aparecer en logs.

### SEC-28: Cookie secure = false para Sitio HTTPS
**Archivo:** `includes/config.php:34`

### SEC-29: Filtros GET Sin Validación de Valores Permitidos
**Archivo:** `public/listado-vehiculos.php:17-30`

### SEC-30: Códigos Promocionales Sin Rate Limiting
**Archivo:** `public/planes.php:114-132`

### SEC-31: OAuth State Token Sin Expiración
**Archivo:** `includes/google_oauth.php:17-18`

### SEC-32: Logout No Regenera Session ID Previa
Destruye sesión pero no invalida cookies de otros orígenes.

### SEC-33: Health Check Expone Información del Servidor
Versiones de PHP, MySQL, extensiones, rutas.

### SEC-34: Contactar Sin Longitud Máxima de Mensaje
**Archivo:** `public/contactar.php:29`

---

## BAJOS

### SEC-35: Google Client ID Visible en JS (Por Diseño)
Client ID es público, no es vulnerabilidad.

### SEC-36: Polyfills PHP 7.x Innecesarios en PHP 8.3
No representa riesgo pero agrega código muerto.

---

## CHECKLIST DE VERIFICACIÓN POST-CORRECCIÓN

- [ ] Credenciales eliminadas de config.php
- [ ] Token de health check movido a .env
- [ ] functions.php sin cierres ?> intermedios
- [ ] CSRF en mis-publicaciones.php
- [ ] CSRF en recuperar-password.php
- [ ] Session completa en OAuth endpoints
- [ ] LIMIT/OFFSET parametrizados
- [ ] reset-password.php con llave corregida
- [ ] TLS verificado en SMTP
- [ ] Open redirect corregido en oauth-google.php
- [ ] Headers de seguridad agregados
- [ ] Rate limiting en login y recuperación
- [ ] getimagesize() en uploads
- [ ] Password policy unificada
- [ ] Session cookies configuradas

---

*Documento generado 2026-04-04. Revisión pendiente tras correcciones.*
