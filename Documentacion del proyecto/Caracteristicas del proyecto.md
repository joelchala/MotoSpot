# MotoSpot — Características del Proyecto

---

## Descripción General

**MotoSpot** es un marketplace de compra y venta de vehículos y embarcaciones orientado al mercado latinoamericano. Permite a usuarios individuales y agencias publicar, buscar y contactar vendedores de forma segura.

- **URL:** https://php.autolatino.site
- **Stack:** PHP 8.3 puro (sin framework), MySQL/MariaDB, HTML/CSS/JS vanilla
- **Hosting:** Hostinger — Plan Business (server547, Singapur)
- **CDN:** Hostinger CDN activo — vaciar caché tras cada deploy

---

## Planes Disponibles

| Plan | Precio/mes | Precio/año | Publicaciones | Duración | Fotos |
|---|---|---|---|---|---|
| Gratis | $0 | $0 | 1 | 30 días | 5 |
| Destacado | $9.99 | $7.99 | 3 | 45 días | 10 |
| Premium | $24.99 | $19.99 | Ilimitadas | 60 días | 20 |
| Premium Plus | $49.99 | $39.99 | Ilimitadas | 120 días | 20 |

**Premium Plus** incluye publicidad constante en redes sociales + gestor de cuenta dedicado + informe mensual.

---

## Tecnologías y Servicios

### Backend
- PHP 8.3.30
- MariaDB 11.8.6
- PDO con prepared statements
- Sesiones PHP nativas
- CSRF tokens
- Logger con rotación de archivos

### Frontend
- HTML5 + CSS3 + JavaScript vanilla
- Google Fonts (Inter)
- Font Awesome 6.4
- AOS (Animate On Scroll)

### APIs Externas
| Servicio | Uso | Plan |
|---|---|---|
| Cloudinary | CDN alternativo para imágenes | Free |
| Unsplash | Imágenes de vehículos (seed/stock) | Free — 50 req/hora |
| Pexels | Imágenes de respaldo | Free — 200 req/hora |
| Pixabay | Fotos + videos para canal de videos | Free — 100 req/60s |

### Infraestructura
| Recurso | Valor |
|---|---|
| Disco | 50 GB |
| RAM | 3072 MB |
| CPU | 2 núcleos |
| PHP Workers | 60 |
| Max. procesos | 120 |
| Backup | India (Bombay) |

---

## Estructura del Servidor

```
/home/u986675534/domains/php.autolatino.site/
├── .motospot/
│   └── .env                    ← Variables de entorno (fuera de public_html)
├── public_html/
│   ├── .htaccess               ← Routing + seguridad
│   ├── index.php               ← Entrada principal → llama a public/index.php
│   ├── assets/                 ← CSS, JS, imágenes estáticas
│   ├── cron/                   ← Scripts de cron jobs
│   │   ├── process_emails.php  ← Cola de emails (cada 5 min)
│   │   ├── cleanup_orphans.php ← Limpieza de imágenes (diario 3am)
│   │   └── rotate_logs.php     ← Rotación de logs (domingos 4am)
│   ├── includes/               ← Módulos PHP reutilizables
│   │   ├── env.php             ← Cargador de .env
│   │   ├── db.php              ← Conexión PDO
│   │   ├── auth.php            ← Autenticación y sesiones
│   │   ├── logger.php          ← Sistema de logs
│   │   ├── mailer.php          ← Cola de emails (queueEmail)
│   │   ├── cloudinary.php      ← Integración Cloudinary
│   │   ├── stock_media.php     ← Unsplash + Pexels + Pixabay
│   │   ├── config.php          ← Configuración del sitio
│   │   ├── functions.php       ← Funciones auxiliares
│   │   ├── header.php          ← Head HTML
│   │   ├── navbar.php          ← Barra de navegación
│   │   └── footer.php          ← Pie de página
│   └── public/                 ← Páginas PHP del sitio
│       ├── index.php           ← Landing page
│       ├── listado-vehiculos.php
│       ├── detalle-vehiculo.php
│       ├── publicar-vehiculo.php
│       ├── embarcaciones.php
│       ├── publicar-embarcacion.php
│       ├── planes.php
│       ├── login.php
│       ├── register.php
│       ├── logout.php
│       ├── perfil.php
│       ├── mis-publicaciones.php
│       ├── contactar.php
│       ├── health.php          ← Diagnóstico del servidor
│       └── image.php           ← Servidor seguro de imágenes
└── storage/                    ← Fuera de public_html
    ├── logs/                   ← Archivos de log (.log)
    ├── uploads/                ← Imágenes subidas por usuarios
    └── cache/                  ← Cache de APIs (Pixabay 24h TOS)
```

---

## Tablas de Base de Datos

| Tabla | Descripción |
|---|---|
| ms_usuarios | Usuarios registrados (individual / agencia) |
| ms_vehiculos | Publicaciones de vehículos |
| ms_vehiculo_fotos | Fotos vinculadas a vehículos |
| ms_contactos | Mensajes de contacto entre usuarios |
| ms_favoritos | Vehículos guardados como favoritos |
| ms_mensajes | Mensajería interna |
| ms_pujas | Sistema de pujas/subastas |
| ms_subastas | Subastas de vehículos |
| ms_sesiones | Sesiones de usuario |
| ms_menus | Menús del sistema |
| ms_paginas | Páginas CMS |
| ms_configuracion | Configuración general del sitio |
| ms_email_queue | Cola de emails asíncronos |

---

## Cron Jobs Configurados

| Expresión | Script | Frecuencia |
|---|---|---|
| `0 3 * * *` | `cron/cleanup_orphans.php` | Diario a las 3:00 AM |
| `*/5 * * * *` | `cron/process_emails.php` | Cada 5 minutos |
| `0 4 * * 7` | `cron/rotate_logs.php` | Domingos a las 4:00 AM |

---

## Routing

Las páginas están en `/public_html/public/` pero se acceden desde la raíz mediante `.htaccess`:

```
https://php.autolatino.site/listado-vehiculos.php
  → reescribe a → public_html/public/listado-vehiculos.php
```

El acceso directo a `/public/` redirige automáticamente a `/` (301).

---

## Seguridad

- Variables de entorno en `.motospot/.env` fuera de `public_html`
- Contraseñas hasheadas con bcrypt (`password_hash`)
- CSRF tokens en formularios
- Headers de seguridad vía `.htaccess` (X-Frame-Options, X-XSS-Protection, etc.)
- Acceso directo a `includes/` bloqueado por `.htaccess`
- Archivos `.env`, `.sql`, `.log` bloqueados por `.htaccess`
- `health.php` protegido por token: `?token=ms_check_2026`
- `logErrors` PHP: OFF en producción (activar en hPanel para debug)

---

## Deploy / Workflow

1. Editar archivos localmente en `C:\MotoSpot php\php.autolatino.site\`
2. Subir via FileZilla a `/domains/php.autolatino.site/` **o** via SCP/SSH
3. Vaciar caché del CDN en **hPanel → CDN → Vaciar caché**
4. Verificar en `https://php.autolatino.site/health.php?token=ms_check_2026`

---

## Notas Importantes

- **SSH disponible:** Puerto `65002`, usuario `u986675534`, clave en `C:\Users\joel.chala\.ssh\hostinger_ed25519`
- **CDN activo:** siempre vaciar caché tras subir cambios
- **Pixabay TOS:** cache de 24h obligatorio, no hotlinking permanente
- **Nameservers externos:** `ns1.dns-parking.com` / `ns2.dns-parking.com` (no son los de Hostinger)
- **Zona horaria:** UTC en servidor; el código aplica `America/Argentina/Buenos_Aires` vía PHP
