# 🔐 CSRF Protection - Guía de Implementación

**Archivo Principal:** `/includes/auth.php`  
**Líneas Relevantes:** 
- `generarCSRFToken()` - Línea 184
- `verificarCSRFToken()` - Línea 191

**Creadas:** 04 de Abril, 2026  
**Estado:** ✅ Implementadas en 5 formularios

---

## 🎯 ¿Qué es CSRF (Cross-Site Request Forgery)?

**CSRF** es un ataque donde un sitio malicioso intenta realizar acciones en otro sitio aprovechando la sesión del usuario.

### Ejemplo de Ataque CSRF

```
1. Usuario inicia sesión en php.autolatino.site (obtiene cookie de sesión)
2. Usuario (sin cerrar sesión) visita maliciousSite.com
3. maliciousSite.com hace solicitud POST a:
   POST /publicar-vehiculo.php
   formulario oculto con datos falsos
4. El navegador envía automáticamente la cookie de sesión
5. El servidor cree que es el usuario legítimo y publica un vehículo falso
```

### Protección: CSRF Tokens

Un **CSRF token** es un valor único por sesión que:
- Se genera aleatoriamente en el servidor
- Se incluye en cada formulario
- Se verifica antes de procesar
- **NO** se envía automáticamente en cookies
- Previene que sitios maliciosos hagan solicitudes válidas

Si maliciousSite.com no conoce el token válido, su solicitud será rechazada.

---

## 🔧 Cómo Funcionan los Tokens

### 1. Generación del Token

```php
// En /includes/auth.php - Línea 184
function generarCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
```

**Características:**
- Se genera UNA SOLA VEZ por sesión
- Se almacena en `$_SESSION['csrf_token']`
- Usa `random_bytes()` para máxima entropía
- Convertido a hex para fácil manipulación

---

### 2. Verificación del Token

```php
// En /includes/auth.php - Línea 191
function verificarCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}
```

**Características:**
- Compara tokens usando `hash_equals()` (timing-safe comparison)
- Previene timing attacks
- Retorna `true` si token válido, `false` si no
- Verifica que sesión exista y token coincida

**Por qué usar `hash_equals()`:**
```php
// ❌ MAL - Vulnerable a timing attack
if ($_SESSION['csrf_token'] === $token) { ... }

// ✅ BIEN - Timing-safe
if (hash_equals($_SESSION['csrf_token'], $token)) { ... }
```

Explicación: Comparación regular termina apenas encuentra diferencia. Un atacante puede medir tiempos de respuesta para adivinar caracteres del token.

---

## 📋 Formularios CON CSRF Implementado ✅

### 1. login.php

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ VERIFICACIÓN CSRF
    if (!verificarCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Token CSRF inválido. Por favor intente nuevamente.';
        header("Location: /login.php");
        exit;
    }
    
    // ... resto de lógica de login
}
?>

<!-- Formulario -->
<form method="POST" action="/login.php">
    <!-- ✅ TOKEN CSRF EN EL FORMULARIO -->
    <input type="hidden" name="csrf_token" value="<?php echo generarCSRFToken(); ?>">
    
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <button type="submit">Iniciar Sesión</button>
</form>
```

---

### 2. register.php

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ VERIFICACIÓN CSRF
    if (!verificarCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Token CSRF inválido. Por favor intente nuevamente.';
        header("Location: /register.php");
        exit;
    }
    
    // ... resto de lógica de registro
}
?>

<!-- Formulario -->
<form method="POST" action="/register.php">
    <!-- ✅ TOKEN CSRF EN EL FORMULARIO -->
    <input type="hidden" name="csrf_token" value="<?php echo generarCSRFToken(); ?>">
    
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <input type="text" name="nombre" required>
    <button type="submit">Registrarse</button>
</form>
```

---

### 3. publicar-vehiculo.php

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ VERIFICACIÓN CSRF (línea 65)
    if (!verificarCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Token de seguridad inválido';
        exit;
    }
    
    // ... validación de datos
    // ... inserción en base de datos
}
?>

<!-- Formulario -->
<form method="POST" enctype="multipart/form-data">
    <!-- ✅ TOKEN CSRF EN EL FORMULARIO (línea 216) -->
    <input type="hidden" name="csrf_token" value="<?php echo generarCSRFToken(); ?>">
    
    <input type="text" name="marca" required>
    <input type="number" name="año" required>
    <input type="number" step="0.01" name="precio" required>
    <textarea name="descripcion"></textarea>
    <input type="file" name="fotos[]" multiple>
    <button type="submit">Publicar Vehículo</button>
</form>
```

---

### 4. admin-codigos.php

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ VERIFICACIÓN CSRF
    if (!verificarCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Token CSRF inválido';
        exit;
    }
    
    // ... lógica de creación de código
}
?>

<!-- Formulario -->
<form method="POST">
    <!-- ✅ TOKEN CSRF EN EL FORMULARIO -->
    <input type="hidden" name="csrf_token" value="<?php echo generarCSRFToken(); ?>">
    
    <input type="text" name="codigo" required>
    <input type="number" name="duracion_dias" required>
    <select name="plan_destino" required>
        <option value="destacado">Destacado</option>
        <option value="premium">Premium</option>
    </select>
    <button type="submit">Crear Código</button>
</form>
```

---

### 5. contactar.php

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ VERIFICACIÓN CSRF
    if (!verificarCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Token CSRF inválido';
        exit;
    }
    
    // ... envío de email
}
?>

<!-- Formulario de contacto -->
<form method="POST">
    <!-- ✅ TOKEN CSRF EN EL FORMULARIO -->
    <input type="hidden" name="csrf_token" value="<?php echo generarCSRFToken(); ?>">
    
    <input type="email" name="email" required>
    <input type="text" name="nombre" required>
    <textarea name="mensaje" required></textarea>
    <button type="submit">Enviar Mensaje</button>
</form>
```

---

### 6. Otros Formularios con CSRF ✅

- **detalle-vehiculo.php** - Formulario de contacto en detalle del vehículo
- **reset-password.php** - Formulario de reseteo de contraseña

---

## 🔄 Flujo Completo de Verificación

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Usuario ve formulario (GET)                              │
└─────────────────────────────────────────────────────────────┘
          │
          ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. Servidor genera token: generarCSRFToken()                │
│    - Genera random_bytes(32)                                 │
│    - Almacena en $_SESSION['csrf_token']                     │
│    - Envía en HTML: <input name="csrf_token" value="...">   │
└─────────────────────────────────────────────────────────────┘
          │
          ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. Usuario completa formulario (en navegador)               │
│    - Token está en el campo hidden                           │
│    - Usuario hace POST                                       │
└─────────────────────────────────────────────────────────────┘
          │
          ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. Servidor recibe POST (línea 1 del handler)               │
│    if ($_SERVER['REQUEST_METHOD'] === 'POST') {             │
│        if (!verificarCSRFToken($_POST['csrf_token'])) {      │
│            exit('Token inválido');  // ← Rechazar aquí      │
│        }                                                      │
│        // ... procesar datos                                 │
│    }                                                          │
└─────────────────────────────────────────────────────────────┘
          │
          ▼
┌─────────────────────────────────────────────────────────────┐
│ 5. verificarCSRFToken() retorna true/false                  │
│    - Compara hash_equals($_SESSION['csrf_token'], $token)   │
│    - Si true: continuar procesamiento                        │
│    - Si false: rechazar solicitud                            │
└─────────────────────────────────────────────────────────────┘
```

---

## ⚠️ Casos Especiales

### Caso 1: Usuario Abre Múltiples Pestañas
```
Pestaña 1: GET /formulario → Token A
Pestaña 2: GET /formulario → Token A (MISMO)
Pestaña 1: POST con Token A ✅ OK
Pestaña 2: POST con Token A ✅ OK

Nota: El token se almacena en la sesión, no en la pestaña.
Todas las pestañas comparten el mismo token.
```

---

### Caso 2: Usuario Cierra Sesión y Vuelve a Iniciar
```
Sesión 1: Token A almacenado
Usuario cierra sesión (logout)
Sesión 2: Token B (nueva sesión, nuevo token)

POST con Token A ❌ RECHAZADO (sesión expirada)
POST con Token B ✅ OK (token válido en sesión actual)
```

---

### Caso 3: Solicitud Asincrónica (AJAX)
```html
<!-- Opción 1: Enviar token en header -->
<script>
const token = document.querySelector('input[name="csrf_token"]').value;
fetch('/api/endpoint', {
    method: 'POST',
    headers: {
        'X-CSRF-Token': token,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({...})
});
</script>

<!-- En PHP se puede recibir del header -->
<?php
$token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!verificarCSRFToken($token)) {
    http_response_code(403);
    exit('CSRF token invalid');
}
?>
```

---

## 🔍 Debugging

### Ver token actual en sesión
```php
<?php
echo $_SESSION['csrf_token'];  // Ejemplo: a1b2c3d4e5f6...
?>
```

### Verificar que se incluye en formulario
```html
<!-- Inspect element → buscar -->
<input type="hidden" name="csrf_token" value="a1b2c3d4e5f6...">
```

### Loguear verificaciones fallidas
```php
<?php
if (!verificarCSRFToken($_POST['csrf_token'] ?? '')) {
    logger('warning', 'CSRF token validation failed', [
        'user_id' => $_SESSION['usuario_id'] ?? null,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'token_provided' => $_POST['csrf_token'] ?? 'none'
    ]);
    $_SESSION['error'] = 'Token de seguridad inválido';
    exit;
}
?>
```

---

## ✅ Checklist para Nuevos Formularios

Cuando agregues un nuevo formulario con POST:

- [ ] Incluir `<input type="hidden" name="csrf_token" value="<?php echo generarCSRFToken(); ?>">`
- [ ] Verificar token en línea 1 del POST handler: `verificarCSRFToken($_POST['csrf_token'] ?? '')`
- [ ] Loguear intentos fallidos
- [ ] Mostrar mensaje de error claro al usuario
- [ ] No procesar datos si token es inválido
- [ ] Usar `header()` para redirigir después de rechazo

### Template Mínimo

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verificarCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Token de seguridad inválido';
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
    
    // Tu lógica aquí
    // ...
}
?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generarCSRFToken(); ?>">
    <!-- Otros campos -->
    <button type="submit">Enviar</button>
</form>
```

---

## 🎓 Referencias OWASP

- [OWASP CSRF Protection Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html)
- [CWE-352: Cross-Site Request Forgery (CSRF)](https://cwe.mitre.org/data/definitions/352.html)

---

**Documento Creado:** 04 de Abril, 2026  
**Última Actualización:** 04 de Abril, 2026  
**Versión:** 1.0  
**Estado:** ✅ Implementado en 5 formularios

---

## ⚠️ ACTUALIZACIÓN 2026-04-04 — Vulnerabilidades CSRF Detectadas

### Formularios SIN Protección CSRF

| Archivo | Formulario | Riesgo |
|---------|-----------|--------|
| `mis-publicaciones.php` | Pausar/Activar publicaciones | **CRÍTICO** — Atacante puede cambiar estado de publicaciones ajenas |
| `recuperar-password.php` | Solicitar reset de contraseña | **CRÍTICO** — Email bombing, reset no autorizado |

### Implementación Recomendada para mis-publicaciones.php

```php
// Agregar al inicio del bloque POST (línea 18):
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['vehiculo_id'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verificarCSRFToken($csrf_token)) {
        header('Location: /mis-publicaciones.php');
        exit();
    }
    // ... resto del procesamiento
}
```

Y en cada formulario HTML:
```html
<input type="hidden" name="csrf_token" value="<?= generarCSRFToken() ?>">
```

### Implementación Recomendada para recuperar-password.php

```php
// Agregar verificación después de la línea 21:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verificarCSRFToken($csrf_token)) {
        $mensaje = 'Token de seguridad inválido. Intente nuevamente.';
        $tipo = 'error';
    } else {
        // ... procesar email
    }
}
```

### Mejora Recomendada: Token por Formulario

El sistema actual usa un token único por sesión. Para mayor seguridad, implementar tokens por formulario con timestamp:

```php
function generarFormToken(string $formId): string {
    $token = bin2hex(random_bytes(32));
    $_SESSION['form_tokens'][$formId] = [
        'token' => $token,
        'expires' => time() + 3600
    ];
    return $token;
}

function verificarFormToken(string $formId, string $token): bool {
    $stored = $_SESSION['form_tokens'][$formId] ?? null;
    if (!$stored || $stored['expires'] < time()) return false;
    $valid = hash_equals($stored['token'], $token);
    unset($_SESSION['form_tokens'][$formId]); // Un solo uso
    return $valid;
}
```

### Estado Actual de Cobertura CSRF

| Formulario | Token Presente | Verificación | Estado |
|-----------|---------------|-------------|--------|
| login.php | ✅ | ✅ | ✅ OK |
| register.php | ✅ | ✅ | ✅ OK |
| publicar-vehiculo.php | ✅ | ✅ | ✅ OK |
| perfil.php (ambos) | ✅ | ✅ | ✅ OK |
| planes.php | ✅ | ✅ | ✅ OK |
| admin-codigos.php | ✅ | ✅ | ✅ OK |
| reset-password.php | ✅ | ✅ | ✅ OK |
| detalle-vehiculo.php (contacto) | ✅ | ✅ | ✅ OK |
| **mis-publicaciones.php** | ❌ | ❌ | **🔴 VULNERABLE** |
| **recuperar-password.php** | ❌ | ❌ | **🔴 VULNERABLE** |

**Cobertura:** 8/10 formularios protegidos (80%)  
**Pendientes:** 2 formularios críticos sin protección

---

*Última actualización: 2026-04-04 — Revisión Exhaustiva*
