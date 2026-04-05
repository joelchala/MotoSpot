# MotoSpot - Marketplace de Vehículos y Embarcaciones

![PHP Version](https://img.shields.io/badge/PHP-8.3+-blue?style=flat-square)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)
![Status](https://img.shields.io/badge/Status-Beta%20v1.0-yellow?style=flat-square)
![Bugs Fixed](https://img.shields.io/badge/Bugs%20Fixed-20%2F21-brightgreen?style=flat-square)
![New Analysis](https://img.shields.io/badge/New%20Analysis-24%20Issues-orange?style=flat-square)

> Plataforma moderna de compra y venta de vehículos y embarcaciones construida con PHP 8.3 puro (sin framework)

---

## 📸 Demo

```
🌐 Production: https://php.autolatino.site
🔧 Health Check: https://php.autolatino.site/health.php
```

---

## ✨ Características Principales

### 🚗 Gestión de Vehículos
- ✅ Listado completo de vehículos con filtros avanzados
- ✅ Publicación de nuevos vehículos con validación
- ✅ Galería de imágenes con integración de APIs
- ✅ Sistema de favoritos

### 🚤 Gestión de Embarcaciones
- ✅ Sección separada para embarcaciones
- ✅ Características específicas de barcos
- ✅ Búsqueda y filtrado

### 👤 Autenticación y Usuarios
- ✅ Registro con validación mejorada
- ✅ Login con email/password + Google OAuth
- ✅ Recuperación de contraseña segura
- ✅ Tipos de usuarios: Individual y Agencia
- ✅ Perfiles con información completa

### 💳 Planes y Suscripciones
- ✅ Planes: Gratis, Destacado, Premium, Premium Plus
- ✅ Códigos promocionales
- ✅ Historial de canjes
- ✅ Gestión de planes por admin

### 📧 Comunicación
- ✅ Sistema de mensajería entre usuarios
- ✅ Formularios de contacto
- ✅ Email queue asincrónico
- ✅ Plantillas de email customizables

### 🔐 Seguridad
- ✅ CSRF tokens en todos los formularios
- ✅ Validación mejorada de entrada con funciones reutilizables
- ✅ Cookies seguras (httponly, secure, samesite)
- ✅ Prepared statements para prevenir SQL injection
- ✅ Logging de eventos de seguridad
- ✅ Protección contra open redirect attacks
- ✅ Upload validation con MIME type checking
- ✅ Password policy enforcement (OWASP standards)
- ✅ Email injection prevention
- ✅ Phone number validation

### 🎨 APIs Externas
- ✅ Integración Unsplash para imágenes de vehículos
- ✅ Fallback a Pexels si Unsplash falla
- ✅ Fallback final a Pixabay
- ✅ Caché inteligente (respeta TOS de Pixabay)
- ✅ Google OAuth para autenticación
- ✅ Cloudinary para CDN de imágenes

---

## 🛠️ Stack Tecnológico

| Categoría | Tecnología |
|-----------|-----------|
| **Backend** | PHP 8.3 (sin framework) |
| **Base de Datos** | MySQL 5.7+ / MariaDB 10.2+ |
| **Frontend** | HTML5, CSS3, JavaScript vanilla |
| **APIs** | Unsplash, Pexels, Pixabay, Google OAuth, Cloudinary |
| **Servidor** | Apache + .htaccess (mod_rewrite) |
| **Hosting** | Hostinger Server (Asia/Singapore) |

---

## 📋 Requisitos Previos

- **PHP:** 8.3 o superior
- **MySQL/MariaDB:** 5.7 o superior
- **Servidor Web:** Apache con mod_rewrite habilitado
- **Extensiones PHP:** 
  - `mysqli` o `pdo_mysql`
  - `curl`
  - `json`
  - `bcrypt` (integrado en PHP 5.5+)
- **API Keys necesarias:**
  - Google OAuth (Google Cloud Console)
  - Unsplash, Pexels, Pixabay (gratuitos)
  - Cloudinary (gratuito)

---

## 🚀 Instalación Rápida

### 1. Clonar repositorio

```bash
git clone https://github.com/joelchala/motospot.git
cd motospot
```

### 2. Configurar variables de entorno

```bash
# Crear directorio de configuración
mkdir -p .motospot

# Copiar template de configuración
cp config/.env.example .motospot/.env

# Editar .motospot/.env con tus credenciales
nano .motospot/.env
```

### 3. Crear base de datos

```bash
mysql -u root -p
CREATE DATABASE motospot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE motospot;

# Importar esquema
SOURCE Documentacion\ del\ proyecto/DATABASE_SCHEMA.sql;

# Crear usuario específico
CREATE USER 'motospot_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON motospot.* TO 'motospot_user'@'localhost';
FLUSH PRIVILEGES;
```

### 4. Crear directorios de almacenamiento

```bash
# Crear estructura de directorios (fuera de public_html)
mkdir -p storage/{uploads,logs,cache}
chmod 755 storage/{uploads,logs,cache}
```

### 5. Ejecutar servidor local

```bash
# Opción 1: PHP built-in server
php -S localhost:8000 -t public_html

# Opción 2: Con Apache (requiere configuración vhost)
# Ver Documentacion/INSTALACION.md para detalles

# Acceder a:
# http://localhost:8000
```

### 6. Crear usuario admin (opcional)

```php
<?php
// En public_html/crear-admin.php
define('MOTO_SPOT', true);
require_once __DIR__ . '/../includes/env.php';
loadEnv();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$email = 'tu@email.com';
$password = password_hash('secure_password', PASSWORD_BCRYPT);

query(
    "INSERT INTO ms_usuarios (email, password, nombre, tipo, estado) VALUES (?, ?, ?, ?, ?)",
    [$email, $password, 'Admin', 'individual', 'activo']
);

echo "✅ Admin creado: $email";
?>
```

---

## 📚 Documentación Completa

| Documento | Propósito |
|-----------|-----------|
| [INSTALACION.md](Documentacion/INSTALACION.md) | Guía de instalación detallada |
| [REPORTE_BUGS.md](Documentacion/REPORTE_BUGS_MOTOSPOT.md) | Bugs reportados y reparaciones (14/18) |
| [REPORTE_BUGS_SESION_NUEVA.md](Documentacion/REPORTE_BUGS_SESION_NUEVA.md) | **NUEVO:** Análisis exhaustivo - 24 issues encontrados (2026-04-04) |
| [VALIDACION_FUNCIONES.md](Documentacion/VALIDACION_FUNCIONES.md) | Sistema de validación de entrada |
| [CSRF_PROTECTION.md](Documentacion/CSRF_PROTECTION.md) | Implementación de CSRF tokens |
| [API_FALLBACK_CHAIN.md](Documentacion/API_FALLBACK_CHAIN.md) | Fallback automático de APIs |
| [DATABASE_SCHEMA.md](Documentacion/DATABASE_SCHEMA.md) | Esquema de base de datos |
| [DEPLOYMENT.md](Documentacion/DEPLOYMENT.md) | Guía de deployment a producción |

---

## 🔧 Configuración de APIs

### Unsplash
```bash
1. Ir a https://unsplash.com/oauth/applications
2. Crear nueva aplicación
3. Copiar Access Key
4. Agregar a .motospot/.env: UNSPLASH_API_KEY=xxxxx
```

### Google OAuth
```bash
1. Ir a https://console.cloud.google.com
2. Crear proyecto
3. Crear credenciales (OAuth 2.0)
4. Agregar URLs autorizadas:
   - http://localhost:8000
   - https://php.autolatino.site
5. Copiar Client ID y Secret
6. Agregar a .motospot/.env: GOOGLE_CLIENT_ID=xxxxx
```

### Pixabay, Pexels, Cloudinary
```
Similar a Unsplash y Google OAuth
Ver config/.env.example para variables requeridas
```

---

## 📊 Estado del Proyecto

### Bugs Reparados: 14/18 (78%) ✅

| ID | Severidad | Estado | Descripción |
|----|-----------|--------|-------------|
| #1 | 🔴 Crítica | ✅ Reparado | stock_media.php missing env.php |
| #3 | 🔴 Crítica | ✅ Reparado | listado-vehiculos.php missing functions.php |
| #5 | 🟡 Media | ✅ Reparado | API calls sin error handling |
| #7 | 🟡 Media | ✅ Reparado | Array index sin seguridad |
| #8 | 🟡 Media | ✅ Reparado | $_SESSION['plan'] no inicializado |
| #9 | 🟡 Media | ✅ Reparado | Falta sanitización |
| #10 | 🟡 Media | ✅ Reparado | array_map incorrecto |
| #13 | 🟡 Media | ✅ Reparado | Cookies sin seguridad |
| #15 | 🟡 Media | ✅ Reparado | CSRF tokens no verificados |
| #16 | 🔴 Alta | ✅ Reparado | Validación insuficiente |
| #17 | 🔴 Alta | ✅ Reparado | CSRF faltantes en más formularios |
| #18 | 🟡 Media | ✅ Reparado | APIs sin fallback |
| #19 | 🟡 Media | ✅ Reparado | API keys expuestas |
| #2 | 🔴 Crítica | ✅ Verificado | Ya incluido correctamente |

**Ver [REPORTE_BUGS_MOTOSPOT.md](Documentacion/REPORTE_BUGS_MOTOSPOT.md) para detalles completos.**

---

## 🔍 Nuevo Análisis de Seguridad (2026-04-04)

### Issues Encontrados: 24 (4 Críticos, 6 Altos, 6 Medios, 8 Bajos)

| Categoría | Crítica | Alta | Media | Baja |
|-----------|---------|------|-------|------|
| **Bugs/Errores código** | 2 | 2 | 2 | 3 |
| **Seguridad** | 1 | 4 | 2 | 3 |
| **Recursos/Conexiones** | 0 | 0 | 2 | 1 |
| **Validación** | 0 | 2 | 1 | 2 |
| **Configuración** | 1 | 0 | 1 | 1 |

**Problemas Críticos Detectados:**
1. Error de sintaxis PHP en `functions.php` (línea 210)
2. Credenciales hardcodeadas en `config.php`
3. Función `logger()` no incluida en `auth.php`
4. Función `logger()` no incluida en `stock_media.php`

**Ver [REPORTE_BUGS_SESION_NUEVA.md](Documentacion/REPORTE_BUGS_SESION_NUEVA.md) para análisis completo.**

---

## 🎯 Funciones de Validación Reutilizables

MotoSpot incluye 7 funciones de validación en `includes/functions.php`:

```php
validarString($value, $min = 0, $max = null)      // Strings con límites
validarInt($value, $min = null, $max = null)      // Enteros con rango
validarFloat($value, $min = null, $max = null)    // Decimales con rango
validarEmail($email)                              // Emails válidos
validarEnum($value, $allowedValues)               // Valores en whitelist
validarAno($year)                                 // Años (1900 - año+1)
validarBooleano($value)                           // Booleanos
```

**Ejemplo de uso:**

```php
// En publicar-vehiculo.php
if (!validarString($_POST['marca'], 2, 50)) {
    $errors['marca'] = 'Marca: 2-50 caracteres';
}

if (!validarFloat($_POST['precio'], 100)) {
    $errors['precio'] = 'Precio mínimo: $100';
}
```

**Ver [VALIDACION_FUNCIONES.md](Documentacion/VALIDACION_FUNCIONES.md) para documentación completa.**

---

## 🔐 Seguridad

### CSRF Protection
```php
// En formularios
<input type="hidden" name="csrf_token" value="<?php echo generarCSRFToken(); ?>">

// En POST handler
if (!verificarCSRFToken($_POST['csrf_token'] ?? '')) {
    exit('Token CSRF inválido');
}
```

### Cookies Seguras
```php
setcookie('usuario_id', $id, [
    'expires' => time() + 86400,
    'path' => '/',
    'httponly' => true,      // Prevenir JS access
    'secure' => true,        // HTTPS only
    'samesite' => 'Strict'   // CSRF prevention
]);
```

### Input Validation
```php
// Todas las entradas validadas
if (!validarEnum($_POST['tipo'], ['individual', 'agencia'])) {
    exit('Tipo inválido');
}

// Prepared statements (protección SQL injection)
query("INSERT INTO ms_usuarios (email, password) VALUES (?, ?)", [$email, $hash]);
```

**Ver [CSRF_PROTECTION.md](Documentacion/CSRF_PROTECTION.md) para más detalles.**

---

## 🌐 API Fallback Chain

Si Unsplash falla, intenta automáticamente:
1. **Unsplash** (50 req/hora) - Mejor calidad
2. **Pexels** (200 req/hora) - Fallback primario
3. **Pixabay** (100 req/60s) - Último recurso

```php
try {
    $results = unsplashSearchImages($query);
} catch (Exception $e) {
    try {
        $results = pexelsSearchImages($query);
    } catch (Exception $e2) {
        $results = pixabaySearchVideos($query);
    }
}
```

**Ver [API_FALLBACK_CHAIN.md](Documentacion/API_FALLBACK_CHAIN.md) para detalles técnicos.**

---

## 🚀 Deployment a Producción

### Pasos Básicos

```bash
# 1. Verificar health check
curl https://php.autolatino.site/health.php?token=ms_check_2026

# 2. Preparar .env con credenciales de producción
# cp config/.env.example .motospot/.env

# 3. Subir código
# git push origin main

# 4. En servidor Hostinger:
#    - FileZilla o SSH (puerto 65002)
#    - Subir archivos
#    - Verificar permisos (755 dirs, 644 files)

# 5. Limpiar caché en hPanel → CDN → Purge

# 6. Verificar logs
tail -f /home/u986675534/storage/logs/motospot.log
```

**Ver [DEPLOYMENT.md](Documentacion/DEPLOYMENT.md) para guía completa.**

---

## 📧 Contacto y Soporte

### Reportar Bugs
```
1. Verificar [REPORTE_BUGS_MOTOSPOT.md](Documentacion/REPORTE_BUGS_MOTOSPOT.md)
2. Abrir issue en GitHub: https://github.com/joelchala/motospot/issues
3. Incluir: descripción, pasos para reproducir, logs
```

### Contacto Directo
- **Email:** joelchala07@gmail.com
- **GitHub:** [@joelchala](https://github.com/joelchala)

---

## 📝 Licencia

Este proyecto está bajo licencia **MIT**. Ver [LICENSE](LICENSE) para detalles.

```
Copyright (c) 2026 Joel Chala

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software...
```

---

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el repositorio
2. Crear rama feature (`git checkout -b feature/AmazingFeature`)
3. Commit cambios (`git commit -m 'Add AmazingFeature'`)
4. Push a rama (`git push origin feature/AmazingFeature`)
5. Abrir Pull Request

Ver [CONTRIBUTING.md](CONTRIBUTING.md) para detalles.

---

## 📊 Estadísticas

| Métrica | Valor |
|---------|-------|
| **Archivos PHP** | 39+ |
| **Módulos Include** | 14 |
| **Páginas Públicas** | 21+ |
| **Funciones de Validación** | 7 |
| **Bugs Reparados** | 14/18 (78%) |
| **Documentación** | 1800+ líneas |
| **Ejemplos de Código** | 50+ |

---

## 🎯 Próximos Pasos

### Completados ✅
- [x] Reparación de 14/18 bugs críticos
- [x] Implementación de CSRF tokens
- [x] Sistema de validación mejorado
- [x] Error handling en APIs
- [x] Documentación completa

### En Progreso 🔄
- [ ] Testing en servidor staging
- [ ] Validación adicional en publicar-embarcacion.php

### Pendientes ⏳
- [ ] Sistema de pagos integrado
- [ ] Cron jobs automáticos
- [ ] Página de administración completa
- [ ] Notificaciones en tiempo real

---

## 📖 Recursos Adicionales

- [PHP Manual](https://www.php.net/manual/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [OWASP Security Guidelines](https://owasp.org/)
- [Web.dev by Google](https://web.dev/)

---

**Última actualización:** 04 de Abril, 2026  
**Versión:** 1.0 Beta  
**Estado:** En desarrollo activo 🚀

---

<div align="center">

**Hecho con ❤️ por [Joel Chala](https://github.com/joelchala)**

⭐ Si te gusta el proyecto, déjale una star en GitHub!

</div>
