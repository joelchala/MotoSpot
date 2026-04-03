# MotoSpot — Documentación del Proyecto

> Marketplace de compra y venta de vehículos y embarcaciones
> Stack: PHP 8+ sin framework · MySQL · HTML/CSS/JS vanilla

**Estado del Proyecto:** ✅ En desarrollo activo  
**Última Actualización:** 4 de Abril 2026  
**Bugs Reparados:** 14/18 (78%)  

---

## 🌐 Acceso al Sitio

| Item | Valor |
|---|---|
| URL producción | https://php.autolatino.site |
| IP servidor | 151.106.116.61 |
| Servidor | server547 — Asia (Singapur) |
| PHP workers | 60 / Máx. procesos: 120 |

---

## 🔐 Credenciales (CONFIDENCIAL)

### Base de Datos MySQL
| Campo | Valor |
|---|---|
| Host (interno) | localhost |
| Host (externo) | srv547.hstgr.io / 194.59.164.108 |
| Base de datos | u986675534_moto |
| Usuario | u986675534_spot |
| Contraseña | AKKuDQ&l~9d |
| Prefijo tablas | ms_ |

### Cuenta Administrador
| Campo | Valor |
|---|---|
| Usuario | admin |
| Email | joelchala07@gmail.com |
| Contraseña | Admin159 |

### FTP / SFTP
| Campo | Valor |
|---|---|
| Host | ftp://php.autolatino.site |
| Usuario | u986675534.php.autolatino.site |
| Contraseña | AKKuDQ&l~9d |
| Puerto SSH | 65002 |
| Usuario SSH | u986675534 |

---

## 📁 Estructura del Proyecto

```
php.autolatino.site/           ← raíz del dominio en servidor
├── .motospot/
│   └── .env                   ← variables de entorno (FUERA de public_html)
└── public_html/               ← raíz web pública
    ├── .htaccess              ← seguridad + routing
    ├── .gitignore
    ├── index.php              ← entrada → public/index.php
    ├── logo.png
    ├── assets/
    │   ├── css/               ← estilos
    │   ├── js/                ← scripts
    │   └── images/            ← imágenes estáticas
    ├── includes/              ← lógica compartida (no accesible vía web)
    │   ├── env.php            ← cargador .env
    │   ├── db.php             ← conexión PDO MySQL
    │   ├── auth.php           ← autenticación y sesiones
    │   ├── functions.php      ← helpers globales
    │   ├── config.php         ← configuración del sitio
    │   ├── logger.php         ← sistema de logs
    │   ├── header.php         ← cabecera HTML
    │   ├── navbar.php         ← barra de navegación
    │   └── footer.php         ← pie de página
    ├── public/                ← páginas PHP accesibles
    │   ├── index.php          ← landing page
    │   ├── listado-vehiculos.php
    │   ├── detalle-vehiculo.php
    │   ├── publicar-vehiculo.php
    │   ├── embarcaciones.php
    │   ├── publicar-embarcacion.php
    │   ├── login.php
    │   ├── register.php
    │   ├── logout.php
    │   ├── perfil.php
    │   ├── mis-publicaciones.php
    │   ├── planes.php
    │   ├── contactar.php
    │   ├── image.php          ← servidor seguro de imágenes
    │   ├── health.php         ← diagnóstico del servidor
    │   └── 404.php            ← página de error personalizada
    └── uploads/
        └── vehiculos/         ← imágenes subidas por usuarios
```

---

## 🗄️ Base de Datos — Tablas

| Tabla | Descripción |
|---|---|
| `ms_usuarios` | Usuarios registrados (particulares y agencias) |
| `ms_vehiculos` | Publicaciones de vehículos |
| `ms_vehiculo_fotos` | Fotos de cada vehículo |
| `ms_favoritos` | Vehículos guardados por usuarios |
| `ms_mensajes` | Mensajes entre compradores y vendedores |

### Roles de usuario (`ms_usuarios.rol`)
| Rol | Descripción |
|---|---|
| `usuario` | Usuario estándar |
| `admin` | Administrador del sistema |

### Tipos de usuario (`ms_usuarios.tipo`)
| Tipo | Descripción |
|---|---|
| `individual` | Vendedor particular |
| `agencia` | Concesionario / agencia |

---

## 🛣️ Rutas del Sitio

| URL | Archivo | Auth requerida |
|---|---|---|
| `/` | `public/index.php` | No |
| `/listado-vehiculos.php` | `public/listado-vehiculos.php` | No |
| `/detalle-vehiculo.php?id=X` | `public/detalle-vehiculo.php` | No |
| `/embarcaciones.php` | `public/embarcaciones.php` | No |
| `/planes.php` | `public/planes.php` | No |
| `/login.php` | `public/login.php` | No (redirige si ya autenticado) |
| `/register.php` | `public/register.php` | No (redirige si ya autenticado) |
| `/publicar-vehiculo.php` | `public/publicar-vehiculo.php` | **Sí** |
| `/perfil.php` | `public/perfil.php` | **Sí** |
| `/mis-publicaciones.php` | `public/mis-publicaciones.php` | **Sí** |
| `/contactar.php` | `public/contactar.php` | No (POST only) |
| `/image.php?f=ruta` | `public/image.php` | No |
| `/health.php?token=ms_check_2026` | `public/health.php` | Token |
| `/logout.php` | `public/logout.php` | No |

---

## ⚙️ Variables de Entorno (.env)

Ubicación: `/domains/php.autolatino.site/.motospot/.env`

| Variable | Descripción |
|---|---|
| `APP_ENV` | Entorno: `production` / `development` |
| `APP_DEBUG` | Mostrar errores: `true` / `false` |
| `DB_HOST` | Host MySQL |
| `DB_NAME` | Nombre de la base de datos |
| `DB_USER` | Usuario MySQL |
| `DB_PASS` | Contraseña MySQL |
| `UPLOAD_PATH` | Ruta absoluta para uploads |
| `LOG_PATH` | Ruta absoluta para logs |
| `LOG_LEVEL` | Nivel mínimo: `debug/info/warning/error` |

---

## 🏗️ Hostinger — Configuración

| Item | Valor |
|---|---|
| CDN | ✅ Activo — vaciar caché tras cambios |
| PHP | 8.x · logErrors OFF · displayErrors OFF |
| TLS | 1.3 |
| Cron jobs | Pendiente configurar |
| Object Storage | Pendiente configurar |
| SMTP | Pendiente configurar |

### Health check
```
https://php.autolatino.site/health.php?token=ms_check_2026
https://php.autolatino.site/health.php?token=ms_check_2026&format=json
```

---

## 📋 Pendientes / Roadmap

### Completados ✅
- [x] Reparación de bugs críticos (stock_media.php, listado-vehiculos.php)
- [x] Implementación de CSRF tokens en formularios principales
- [x] Validación mejorada de entrada en formularios
- [x] Error handling en llamadas a APIs externas
- [x] Fallback automático entre APIs (Unsplash → Pexels → Pixabay)
- [x] Seguridad de cookies (httponly, secure, samesite)
- [x] Protección de API keys en .env

### En Progreso 🔄
- [ ] Testing en servidor staging
- [ ] Validación adicional en publicar-embarcacion.php
- [ ] Mejoras menores de error handling

### Pendientes ⏳
- [ ] Configurar cron jobs (limpieza imágenes, rotación logs)
- [ ] Configurar SMTP transaccional
- [ ] Evaluar Object Storage para uploads
- [ ] Activar `logErrors` en hPanel para debug
- [ ] Implementar sistema de pagos (planes Básico/Premium)
- [ ] Página de administración completa
- [ ] Sistema de notificaciones en tiempo real
- [ ] Crear tablas faltantes en MySQL (ms_mensajes, etc.)
