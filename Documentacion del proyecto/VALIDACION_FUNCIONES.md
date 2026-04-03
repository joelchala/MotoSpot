# 📚 Sistema de Validación de Entrada - Documentación

**Archivo Principal:** `/includes/functions.php`  
**Líneas:** 126-200  
**Creadas:** 04 de Abril, 2026  

---

## 🎯 Descripción General

Se han creado 7 funciones de validación reutilizables que facilitan:
- Validación consistente en todos los formularios
- Prevención de XSS, SQL injection
- Reducción de código duplicado
- Documentación clara de requisitos de datos

Todas las funciones retornan `true` si válido, `false` si no.

---

## 📋 Funciones Disponibles

### 1. `validarString($value, $min = 0, $max = null)`

**Propósito:** Validar strings con límites de longitud  
**Parámetros:**
- `$value` (string): Valor a validar
- `$min` (int): Longitud mínima (default: 0)
- `$max` (int|null): Longitud máxima (default: sin límite)

**Retorna:** `true` si válido, `false` si no

**Ejemplos:**
```php
// Validar marca (2-50 caracteres)
if (!validarString($_POST['marca'], 2, 50)) {
    $errors['marca'] = 'La marca debe tener 2-50 caracteres';
}

// Validar descripción (máximo 2000 caracteres)
if (!validarString($_POST['descripcion'], 1, 2000)) {
    $errors['descripcion'] = 'Máximo 2000 caracteres';
}

// Validar cualquier string (sin límites)
if (!validarString($username)) {
    $errors['username'] = 'Campo requerido';
}
```

---

### 2. `validarInt($value, $min = null, $max = null)`

**Propósito:** Validar números enteros con rango opcional  
**Parámetros:**
- `$value` (mixed): Valor a validar
- `$min` (int|null): Valor mínimo (default: sin límite)
- `$max` (int|null): Valor máximo (default: sin límite)

**Retorna:** `true` si válido, `false` si no

**Ejemplos:**
```php
// Validar cantidad (mínimo 1)
if (!validarInt($_POST['cantidad'], 1)) {
    $errors['cantidad'] = 'Debe ser mayor a 0';
}

// Validar entre rango
if (!validarInt($_POST['año'], 1900, date('Y') + 1)) {
    $errors['año'] = 'Año inválido';
}

// Validar simplemente que sea entero
if (!validarInt($_POST['id'])) {
    $errors['id'] = 'ID inválido';
}
```

---

### 3. `validarFloat($value, $min = null, $max = null)`

**Propósito:** Validar números decimales con rango opcional  
**Parámetros:**
- `$value` (mixed): Valor a validar
- `$min` (float|null): Valor mínimo (default: sin límite)
- `$max` (float|null): Valor máximo (default: sin límite)

**Retorna:** `true` si válido, `false` si no

**Ejemplos:**
```php
// Validar precio (mínimo $100)
if (!validarFloat($_POST['precio'], 100)) {
    $errors['precio'] = 'El precio mínimo es $100';
}

// Validar ponderación entre 0-1
if (!validarFloat($_POST['ponderacion'], 0, 1)) {
    $errors['ponderacion'] = 'Debe estar entre 0 y 1';
}

// Validar que sea número decimal
if (!validarFloat($lat)) {
    $errors['latitude'] = 'Latitud inválida';
}
```

---

### 4. `validarEmail($email)`

**Propósito:** Validar formato de email  
**Parámetros:**
- `$email` (string): Email a validar

**Retorna:** `true` si email válido, `false` si no

**Ejemplos:**
```php
// Validar email en formulario de registro
if (!validarEmail($_POST['email'])) {
    $errors['email'] = 'Email inválido';
}

// Validar email antes de enviar mensaje
if (!validarEmail($destinatario)) {
    logger('warning', 'Invalid email address', ['email' => $destinatario]);
    exit;
}
```

---

### 5. `validarEnum($value, $allowedValues)`

**Propósito:** Validar que valor está en lista permitida (whitelist)  
**Parámetros:**
- `$value` (string): Valor a validar
- `$allowedValues` (array): Array de valores permitidos

**Retorna:** `true` si valor está en lista, `false` si no

**Ejemplos:**
```php
// Validar tipo de usuario
if (!validarEnum($_POST['tipo'], ['individual', 'agencia'])) {
    $errors['tipo'] = 'Tipo de usuario inválido';
}

// Validar estado de vehículo
if (!validarEnum($_POST['estado'], ['activo', 'vendido', 'suspendido'])) {
    $errors['estado'] = 'Estado inválido';
}

// Validar categoría
if (!validarEnum($categoria, ['vehiculos', 'embarcaciones', 'repuestos'])) {
    logger('error', 'Invalid category', ['category' => $categoria]);
    exit;
}
```

---

### 6. `validarAno($year)`

**Propósito:** Validar que año está en rango válido (1900 - año actual + 1)  
**Parámetros:**
- `$year` (mixed): Año a validar

**Retorna:** `true` si año válido, `false` si no

**Ejemplos:**
```php
// Validar año de fabricación del vehículo
if (!validarAno($_POST['año_fabricacion'])) {
    $errors['año_fabricacion'] = 'Año de fabricación inválido';
}

// Validar año en formulario de embarcación
if (!validarAno($boat['year'])) {
    $_SESSION['error'] = 'Año inválido';
    exit;
}
```

---

### 7. `validarBooleano($value)`

**Propósito:** Validar que valor es booleano válido  
**Parámetros:**
- `$value` (mixed): Valor a validar (puede ser '0', '1', 'on', 'off', true, false)

**Retorna:** `true` si es booleano válido, `false` si no

**Ejemplos:**
```php
// Validar checkbox de términos
if (!validarBooleano($_POST['terminos'] ?? false)) {
    $errors['terminos'] = 'Debe aceptar términos y condiciones';
}

// Validar flags de usuario
if (!validarBooleano($user['activo'])) {
    $_SESSION['error'] = 'Estado de usuario inválido';
    exit;
}
```

---

## 🔧 Patrón de Uso Recomendado

### En Formularios (publicar-vehiculo.php style)

```php
<?php
// Incluir funciones
require_once __DIR__ . '/../includes/functions.php';

// Inicializar array de errores
$errors = [];

// Procesar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validar CSRF
    if (!verificarCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Token CSRF inválido';
        exit;
    }
    
    // 2. Validar campos
    if (!validarString($_POST['marca'], 2, 50)) {
        $errors['marca'] = 'Marca: 2-50 caracteres';
    }
    
    if (!validarInt($_POST['año'], 1900, date('Y') + 1)) {
        $errors['año'] = 'Año inválido';
    }
    
    if (!validarFloat($_POST['precio'], 100)) {
        $errors['precio'] = 'Precio mínimo: $100';
    }
    
    if (!validarEnum($_POST['estado'], ['activo', 'suspendido'])) {
        $errors['estado'] = 'Estado inválido';
    }
    
    // 3. Si hay errores, mostrar formulario de nuevo
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
    
    // 4. Si todo OK, procesar datos
    $marca = htmlspecialchars($_POST['marca']);
    $año = (int)$_POST['año'];
    $precio = (float)$_POST['precio'];
    
    // Guardar en base de datos
    query(
        "INSERT INTO " . table('vehiculos') . " (usuario_id, marca, año, precio, estado) VALUES (?, ?, ?, ?, ?)",
        [$_SESSION['usuario_id'], $marca, $año, $precio, $_POST['estado']]
    );
    
    $_SESSION['success'] = 'Vehículo publicado exitosamente';
    redirect('/mis-publicaciones.php');
}
?>

<!-- Formulario con CSRF token -->
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generarCSRFToken(); ?>">
    
    <!-- Marca -->
    <input type="text" name="marca" value="<?php echo htmlspecialchars($_POST['marca'] ?? ''); ?>">
    <?php if (isset($_SESSION['errors']['marca'])): ?>
        <span class="error"><?php echo $_SESSION['errors']['marca']; ?></span>
    <?php endif; ?>
    
    <!-- Año -->
    <input type="number" name="año" value="<?php echo htmlspecialchars($_POST['año'] ?? ''); ?>">
    <?php if (isset($_SESSION['errors']['año'])): ?>
        <span class="error"><?php echo $_SESSION['errors']['año']; ?></span>
    <?php endif; ?>
    
    <!-- Precio -->
    <input type="number" step="0.01" name="precio" value="<?php echo htmlspecialchars($_POST['precio'] ?? ''); ?>">
    <?php if (isset($_SESSION['errors']['precio'])): ?>
        <span class="error"><?php echo $_SESSION['errors']['precio']; ?></span>
    <?php endif; ?>
    
    <!-- Estado -->
    <select name="estado">
        <option value="">Seleccionar estado</option>
        <option value="activo" <?php echo ($_POST['estado'] ?? '') === 'activo' ? 'selected' : ''; ?>>Activo</option>
        <option value="suspendido" <?php echo ($_POST['estado'] ?? '') === 'suspendido' ? 'selected' : ''; ?>>Suspendido</option>
    </select>
    <?php if (isset($_SESSION['errors']['estado'])): ?>
        <span class="error"><?php echo $_SESSION['errors']['estado']; ?></span>
    <?php endif; ?>
    
    <button type="submit">Guardar</button>
</form>

<?php
// Limpiar errores de sesión
unset($_SESSION['errors']);
?>
```

---

## 🛡️ Seguridad

### Protección Incluida
1. **XSS Prevention**: Trim + type casting previene inyección de HTML
2. **SQL Injection**: Prepared statements (responsabilidad del código llamante)
3. **Type Safety**: Validación de tipos antes de usar en base de datos
4. **Whitelist Validation**: `validarEnum()` previene valores inesperados

### Responsabilidades del Código Llamante
1. Siempre usar `htmlspecialchars()` antes de mostrar valores en HTML
2. Siempre usar prepared statements para queries
3. Loguear intentos de validación fallida
4. No confíar solo en validación client-side

### Ejemplo de Uso Seguro Completo

```php
// ✅ CORRECTO - Validación + sanitización + prepared statements
if (!validarString($_POST['titulo'], 1, 200)) {
    $errors['titulo'] = 'Título inválido';
    exit;
}

$titulo = htmlspecialchars($_POST['titulo'], ENT_QUOTES, 'UTF-8');

query(
    "UPDATE " . table('vehiculos') . " SET titulo = ? WHERE id = ?",
    [$titulo, $id]  // Prepared statement protege contra SQL injection
);

echo "Vehículo actualizado: " . htmlspecialchars($titulo);  // Display also sanitized

// ❌ INCORRECTO - Falta sanitización
echo "Vehículo actualizado: " . $_POST['titulo'];  // XSS vulnerability!
```

---

## 📊 Matriz de Uso Recomendado

| Campo | Tipo | Función | Rango |
|-------|------|---------|-------|
| **Marca** | String | `validarString()` | 2-50 |
| **Modelo** | String | `validarString()` | 2-50 |
| **Año Fabricación** | Integer | `validarAno()` | 1900 - año actual + 1 |
| **Precio** | Float | `validarFloat()` | Mínimo $100 |
| **Descripción** | String | `validarString()` | 1-2000 |
| **Tipo Usuario** | Enum | `validarEnum()` | 'individual', 'agencia' |
| **Estado** | Enum | `validarEnum()` | 'activo', 'suspendido', 'vendido' |
| **Email** | Email | `validarEmail()` | Formato válido |
| **Términos** | Boolean | `validarBooleano()` | true/false |

---

## 📈 Mejoras Futuras

1. **Validación de Teléfono**: `validarTelefono($phone, $country_code)`
2. **Validación de URL**: `validarURL($url)`
3. **Validación de Archivo**: `validarArchivo($file, $allowedTypes, $maxSize)`
4. **Validación de Fecha**: `validarFecha($date, $format)`
5. **Validación de Rango de Fechas**: `validarRangoFechas($start, $end)`

---

## 🔍 Testing

Para verificar que las funciones funcionan correctamente:

```php
<?php
// test-validation.php
require_once __DIR__ . '/includes/functions.php';

// Test validarString
assert(validarString('hello', 1, 10) === true);
assert(validarString('', 1, 10) === false);
assert(validarString('hello world', 1, 5) === false);

// Test validarInt
assert(validarInt(10, 1, 100) === true);
assert(validarInt('10', 1, 100) === true);
assert(validarInt(0, 1, 100) === false);

// Test validarEnum
assert(validarEnum('activo', ['activo', 'inactivo']) === true);
assert(validarEnum('borrado', ['activo', 'inactivo']) === false);

// Test validarAno
$currentYear = date('Y');
assert(validarAno(2020) === true);
assert(validarAno($currentYear + 1) === true);
assert(validarAno(1899) === false);
assert(validarAno($currentYear + 2) === false);

echo "✅ Todas las pruebas pasaron\n";
?>
```

---

**Documento Creado:** 04 de Abril, 2026  
**Última Actualización:** 04 de Abril, 2026  
**Versión:** 1.0
