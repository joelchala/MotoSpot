# CLAUDE.md — MotoSpot

Instrucciones y contexto para Claude Code en este proyecto.

---

## Proyecto

**MotoSpot** — Marketplace de vehículos y embarcaciones para Latinoamérica.
- **URL producción:** https://php.autolatino.site
- **Stack:** PHP 8.3 puro (sin framework), MariaDB 11.8.6, PDO, HTML/CSS/JS vanilla
- **Hosting:** Hostinger Business — IP 151.106.116.61
- **CDN:** Hostinger CDN activo — vaciar caché tras cada deploy

---

## Documentación del proyecto

Toda la documentación está en `C:\MotoSpot php\Documentacion del proyecto\`:

| Archivo | Contenido |
|---------|-----------|
| `Caracteristicas del proyecto.md` | Stack, planes, APIs, estructura del servidor, tablas DB, crons, routing, seguridad, workflow de deploy |
| `Usuarios.md` | Lista de usuarios, contraseñas, roles, tipos y credenciales DB/FTP/SSH |
| `README.md` | Introducción general |

**Leer siempre antes de hacer cambios estructurales.**

---

## Estructura de directorios

```
C:\MotoSpot php\
├── CLAUDE.md                          ← este archivo
├── php.autolatino.site\
│   └── public_html\
│       ├── .htaccess                  ← routing principal + seguridad
│       ├── .motospot\.env             ← NO existe aquí, está en el servidor
│       ├── public\                    ← páginas PHP accesibles
│       │   ├── index.php
│       │   ├── login.php
│       │   ├── register.php
│       │   ├── planes.php
│       │   ├── recuperar-password.php
│       │   ├── reset-password.php
│       │   ├── oauth-google.php       ← callback redirect (legacy, ya no usado)
│       │   └── auth-google-token.php  ← endpoint GIS (activo)
│       ├── includes\                  ← helpers internos (no accesibles desde web)
│       │   ├── env.php
│       │   ├── db.php
│       │   ├── auth.php
│       │   ├── mailer.php
│       │   ├── logger.php
│       │   ├── google_oauth.php
│       │   ├── stock_media.php
│       │   └── cloudinary.php
│       └── cron\                      ← solo CLI, bloqueados desde web
│           ├── process_emails.php
│           ├── cleanup_orphans.php
│           └── rotate_logs.php
├── Base de datos\
│   ├── email_queue.sql
│   └── auth_extras.sql
└── Documentacion del proyecto\
    ├── Caracteristicas del proyecto.md
    ├── Usuarios.md
    └── README.md
```

---

## Servidor (SSH / FTP)

- **SSH:** `ssh -i C:/Users/joel.chala/.ssh/hostinger_ed25519 -p 65002 u986675534@151.106.116.61`
- **FTP MCP site ID:** `1f7e0755-ef27-4375-867f-3679f6409a70` (Auto Spot php)
- **Ruta raíz servidor:** `/home/u986675534/domains/php.autolatino.site/`
- **public_html:** `/home/u986675534/domains/php.autolatino.site/public_html/`
- **storage:** `/home/u986675534/domains/php.autolatino.site/storage/`
- **`.env` en servidor:** `/home/u986675534/domains/php.autolatino.site/.motospot/.env`

### Deploy de archivos

Siempre via SCP:
```bash
scp -i "C:/Users/joel.chala/.ssh/hostinger_ed25519" -P 65002 archivo.php \
  u986675534@151.106.116.61:/home/u986675534/domains/php.autolatino.site/public_html/public/
```
Después de cada deploy: **vaciar caché del CDN en hPanel**.

---

## Base de datos

- **Host:** localhost
- **DB:** u986675534_moto
- **User:** u986675534_spot
- **Prefijo tablas:** `ms_`
- **Tablas principales:** `ms_usuarios`, `ms_vehiculos`, `ms_vehiculo_imagenes`, `ms_password_resets`, `ms_email_queue`

---

## Reglas del proyecto

1. **PHP puro** — sin Composer, sin frameworks. Toda dependencia externa se implementa con cURL.
2. **Routing:** Las URLs limpias (`/login.php`) se sirven desde `/public/` via `.htaccess`. No crear archivos PHP en la raíz de `public_html`.
3. **`.env` fuera de public_html** — en `/domains/php.autolatino.site/.motospot/.env`. Nunca dentro de `public_html`.
4. **CDN activo** — vaciar caché tras cada deploy o los cambios no se verán.
5. **Emails en cola** — no enviar SMTP directo desde páginas web. Usar `queueEmail()` y dejar que el cron lo procese.
6. **Google OAuth** — usar flujo GIS (Google Identity Services) con POST token a `/auth-google-token.php`. El flujo redirect (`oauth-google.php`) está desactivado porque ModSecurity de Hostinger bloquea la callback URL con query params de scope.
7. **APIs stock media** — Pixabay requiere caché 24h por TOS. Toda llamada pasa por `stock_media.php` que gestiona el caché en `storage/cache/`.
8. **Timeouts cURL** — siempre definir `CURLOPT_TIMEOUT` y `CURLOPT_CONNECTTIMEOUT`.
9. **Cron jobs** — solo ejecutables desde CLI (`php_sapi_name() !== 'cli'` como guard).
10. **Validar sintaxis** antes de dar por terminado: `php -l archivo.php` via SSH.
