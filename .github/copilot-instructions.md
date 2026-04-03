# MotoSpot — Copilot Instructions

This is a **PHP 8.3 marketplace** (no framework) for buying/selling vehicles and boats. Hosted on Hostinger with MySQL/MariaDB, vanilla JS/CSS frontend, and integrations with external APIs (Cloudinary, Unsplash, Pexels, Pixabay).

---

## 🏗️ Architecture

### Directory Structure

```
php.autolatino.site/
├── .motospot/
│   └── .env                 ← Environment variables (NEVER in public_html)
└── public_html/             ← Web root
    ├── includes/            ← Reusable modules
    │   ├── env.php          ← Loads .env
    │   ├── db.php           ← PDO MySQL connection
    │   ├── config.php       ← Site configuration
    │   ├── functions.php    ← Global helpers
    │   ├── auth.php         ← Auth & sessions
    │   ├── header.php       ← HTML <head>
    │   ├── navbar.php       ← Navigation
    │   ├── footer.php       ← Footer
    │   ├── logger.php       ← Logging system
    │   ├── mailer.php       ← Email queue
    │   ├── stock_media.php  ← Unsplash/Pexels/Pixabay integration
    │   ├── cloudinary.php   ← Cloudinary CDN
    │   └── google_oauth.php ← Google OAuth
    ├── public/              ← Accessible pages (routed via .htaccess)
    │   ├── index.php        ← Landing page
    │   ├── listado-vehiculos.php
    │   ├── detalle-vehiculo.php
    │   ├── publicar-vehiculo.php
    │   ├── login.php, register.php, logout.php
    │   ├── perfil.php, mis-publicaciones.php
    │   ├── planes.php       ← Subscription plans & promo codes
    │   ├── health.php       ← Server health check
    │   ├── image.php        ← Secure image serving
    │   └── 404.php
    ├── assets/              ← CSS, JS, static images
    ├── uploads/             ← User-uploaded images
    ├── cron/                ← Scheduled jobs
    │   ├── cleanup_orphans.php
    │   ├── process_emails.php
    │   └── rotate_logs.php
    ├── index.php            ← Entry point → requires public/index.php
    └── .htaccess            ← Routing + security headers
```

### Include Dependencies

**Critical order** (must be respected):

1. `env.php` — Loads environment variables from `.motospot/.env`
2. `db.php` — Creates PDO connection (depends on `env.php`)
3. `config.php` — Site configuration
4. `functions.php` — Helper functions (depends on `config.php`)
5. `auth.php` — User authentication (depends on `db.php` + `functions.php`)

**Pattern for page includes:**

```php
<?php
require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

// Your code here

include __DIR__ . '/../includes/footer.php';
```

---

## 🔑 Key Conventions

### Database

- **Table prefix:** `ms_` (all tables: `ms_usuarios`, `ms_vehiculos`, etc.)
- **Helper function:** Use `table('usuarios')` instead of `'ms_usuarios'` for portability
- **Queries:** Always use prepared statements via `query()`, `fetchOne()`, `fetchAll()`
- **Constants:** Define `MOTO_SPOT` before including `functions.php` or `config.php`

Example:
```php
$usuario = fetchOne("SELECT * FROM " . table('usuarios') . " WHERE id = ?", [$id]);
$vehiculos = fetchAll("SELECT * FROM " . table('vehiculos') . " WHERE estado = ?", ['activo']);
```

### Authentication & Sessions

- **Session key:** `usuario_id` in `$_SESSION`
- **Admin check:** Use `esAdmin()` function — checks if logged-in user's email is `joelchala07@gmail.com`
- **Agency check:** Use `esAgencia()` function — checks `tipo = 'agencia'`
- **CSRF tokens:** Generated with `generarCSRFToken()`, verified with `verificarCSRFToken()`
- **Check auth:** Use `requerirAutenticacion()` to redirect unauthenticated users to login
- **User types:** `individual` (private seller), `agencia` (agency/dealership)
- **User states:** `activo`, `inactivo`, `suspendido`

**Auth functions available:**
- `estaAutenticado()` — Returns true if logged in
- `esAdmin()` — Returns true if admin
- `esAgencia()` — Returns true if user is an agency
- `getUsuarioId()` — Returns current user's ID
- `getUsuarioActual()` — Returns full user object
- `login($email, $password)` — Authenticate user
- `crearSesionUsuario($usuario)` — Create session after login
- `registrarUsuario($datos)` — Register new account
- `logout()` — Destroy session
- `requerirAutenticacion()` — Redirect to login if not authenticated
- `generarCSRFToken()` — Create CSRF token
- `verificarCSRFToken($token)` — Validate CSRF token
- `regenerarSesion()` — Regenerate session ID

### Error Handling

- **API failures:** Wrap external API calls (Unsplash, Pexels, Pixabay) in try-catch with fallbacks
- **Database errors:** PDO exceptions are caught; log with `logger()` function
- **User feedback:** Use `$_SESSION['error']` / `$_SESSION['success']` for flash messages
- **404 handling:** `.htaccess` routes to `public/404.php` for undefined pages

### Logging

- **Function:** `logger($level, $message, $context = [])`
- **Levels:** `debug`, `info`, `warning`, `error`, `critical`
- **Location:** Logs written to `storage/logs/` (outside `public_html`)
- **Format:** ISO 8601 timestamps + context array
- **Rotation:** Automatic daily rotation via `cron/rotate_logs.php` (Sundays 4am)

Example:
```php
logger('warning', 'Failed login attempt', ['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR']]);
```

### Email Handling

Emails are **queued asynchronously** — not sent immediately. Use `queueEmail()`:

```php
queueEmail(
    toEmail: 'user@example.com',
    toName: 'Juan García',
    subject: 'Tu vehículo ha recibido un contacto',
    bodyHtml: emailTemplate('Título', 'Contenido HTML', 'Botón', 'https://url'),
    metadata: ['vehiculo_id' => 123, 'tipo' => 'nuevo_contacto']
);
// Actual sending happens via cron/process_emails.php every 5 minutes
// Retries failed sends up to configured limit
```

### Promo Codes & Plans

Plans are: `gratis` (default), `destacado`, `premium`, `premium_plus`

Promo codes can be redeemed via `planifyPage()` (in `planes.php`). Each code:
- Is linked to a plan (`plan_destino`)
- Has duration in days (`duracion_dias` — typically 30)
- Can only be used once (`usado` = 0 or 1)
- Records redemption history in `ms_historial_codigos`
- Updates user's `codigo_promo_activo` and `codigo_promo_hasta`

Admin page: `public/admin-codigos.php` (admin-only)

### File Uploads

- **Storage:** Files stored outside `public_html` (in `/storage/uploads/`)
- **Serving:** Use `image.php?f=path/to/image.jpg` for security
- **Cloudinary integration:** See `includes/cloudinary.php` for CDN fallback
- **Validation:** Check file size, type, and extension before saving

### Environment Variables

Located in `/.motospot/.env` (outside `public_html`). Common vars:

- `APP_ENV` — `production` or `development`
- `APP_DEBUG` — `true` or `false`
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` — MySQL credentials
- `UPLOAD_PATH` — Absolute path for uploads (e.g., `/home/user/storage/uploads/`)
- `LOG_PATH` — Absolute path for logs (e.g., `/home/user/storage/logs/`)

Access via `env('VAR_NAME', 'default')` — loads from .env or returns default.

### External APIs

- **Unsplash:** 50 requests/hour, used for stock vehicle photos (`unsplashSearchImages()`)
- **Pexels:** 200 requests/hour, fallback source (`pexelsSearchImages()`)
- **Pixabay:** 100 requests/60s, used for video/banner images (`pixabaySearchVideos()`)
  - **Important:** Pixabay TOS requires 24h cache; never direct hotlink permanently
- **Google OAuth:** Login via Google (see `google_oauth.php`, `auth-google-token.php`)
- **Cloudinary:** Free tier CDN for user uploads (see `cloudinary.php`)

All integrated in `stock_media.php`. API calls should have fallbacks.

---

## 🛠️ Development & Deployment

### Local Testing

No build step needed — pure PHP. Test locally with:
```bash
php -S localhost:8000 -t php.autolatino.site/public_html
```

Then access `http://localhost:8000/`.

### Deployment

1. **Edit files** in `php.autolatino.site/public_html/`
2. **Upload to server** via FileZilla or SSH (port 65002)
3. **Clear CDN cache** in Hostinger hPanel → CDN → Purge
4. **Verify** at `https://php.autolatino.site/health.php?token=ms_check_2026`

### Health Check

```bash
# Basic health check
curl "https://php.autolatino.site/health.php?token=ms_check_2026"

# JSON format
curl "https://php.autolatino.site/health.php?token=ms_check_2026&format=json"
```

---

## 📄 Main Pages & User Flows

### Public Pages (No Auth Required)

| Page | File | Purpose |
|---|---|---|
| **Landing** | `public/index.php` | Hero section, category cards, featured videos from Pixabay |
| **Vehicle Listing** | `public/listado-vehiculos.php` | Searchable catalog with filters (tipo, precio, año, etc.) |
| **Vehicle Detail** | `public/detalle-vehiculo.php` | Single vehicle with photos, specs, contact form |
| **Boats** | `public/embarcaciones.php` | Separate listing for boats |
| **Plans** | `public/planes.php` | Subscription tiers + promo code redemption |
| **Login** | `public/login.php` | Email/password + Google OAuth (see `auth-google-token.php`) |
| **Register** | `public/register.php` | New account creation (individual or agency) |
| **Contact** | `public/contactar.php` | Contact form (sends email to seller) |
| **Password Recovery** | `public/recuperar-password.php` | Request reset token |
| **Reset Password** | `public/reset-password.php` | Set new password with token |
| **Health Check** | `public/health.php` | Server diagnostics (requires `?token=ms_check_2026`) |
| **Image Server** | `public/image.php` | Secure image serving (validates path, prevents directory traversal) |

### Protected Pages (Auth Required)

| Page | File | Purpose |
|---|---|---|
| **User Profile** | `public/perfil.php` | Edit name, type, agency info (if agencia) |
| **My Listings** | `public/mis-publicaciones.php` | View/edit/delete user's own vehicles |
| **Publish Vehicle** | `public/publicar-vehiculo.php` | Create new vehicle listing |
| **Publish Boat** | `public/publicar-embarcacion.php` | Create new boat listing |
| **Promo Codes** | `public/admin-codigos.php` | Admin-only: create/manage promo codes |
| **Logout** | `public/logout.php` | Destroy session |

### Auth Flows

**Login Flow:**
1. User enters email + password in `login.php`
2. `login()` function checks credentials against bcrypt hash in DB
3. If valid: `crearSesionUsuario()` creates session + sets cookies
4. Session key: `usuario_id`, also stores user object in memory

**Google OAuth Flow:**
1. User clicks "Login with Google" in `login.php`
2. Redirects to Google's OAuth endpoint (see `oauth-google.php`)
3. After Google approval, returns to `auth-google-token.php`
4. Token exchanged for user info, account created if new
5. `crearSesionUsuario()` creates session

**Password Reset Flow:**
1. User goes to `recuperar-password.php`, enters email
2. Token generated, stored in `ms_password_resets` with expiration
3. Email queued via `queueEmail()` with reset link
4. User clicks link → `reset-password.php?token=XYZ`
5. Validates token expiration + marks as used
6. Updates password in DB

---

## 🐛 Common Pitfalls & Known Issues

### Critical Bugs (High Priority)

From `REPORTE_BUGS_MOTOSPOT.md`:

1. **`stock_media.php` missing `env.php` include** — Line 21 calls `env()` without defining it
2. **`auth.php` missing `functions.php` include** — Calls `fetchOne()`, `executeQuery()` undefined
3. **`listado-vehiculos.php` missing `functions.php`** — Line 92 calls `fetchOne()` undefined
4. **`str_starts_with()` in `login.php`** — Only available in PHP 8.0+; use polyfill for compatibility

### Security Gaps

1. **CSRF tokens not enforced** — Functions exist but not used in forms
2. **API keys hardcoded** — Google Client ID in `login.php` (move to `.env`)
3. **Inadequate input validation** — Some forms lack sanitization (e.g., `admin-codigos.php` doesn't sanitize notes)

### Edge Cases

- **Array index errors** — `planes.php` line 356 assumes fixed number of CSS classes
- **Circular dependencies** — Include order is critical; if changed, things break
- **Missing session variables** — `$_SESSION['plan']` not always initialized on login

---

## 📊 Database Schema Summary

**Core tables:**

| Table | Purpose |
|---|---|
| `ms_usuarios` | User accounts (type: individual/agencia). Admin detected by email `joelchala07@gmail.com` |
| `ms_vehiculos` | Vehicle listings with state/status tracking |
| `ms_vehiculo_fotos` | Photos per vehicle (linked by vehiculo_id) |
| `ms_favoritos` | Bookmarked vehicles by users |
| `ms_mensajes` | Direct messages between buyers and sellers |
| `ms_email_queue` | Async email queue (status: pending/processing/sent/failed) |
| `ms_contactos` | Contact form submissions |
| `ms_configuracion` | Site configuration |
| `ms_password_resets` | Password reset tokens (with expiration) |
| `ms_codigos_promocionales` | Promo codes for plan upgrades |
| `ms_historial_codigos` | Redemption history for promo codes |

**User columns:**
- `tipo` — `individual` or `agencia`
- `estado` — `activo`, `inactivo`, or `suspendido`
- `google_id` — OAuth integration (nullable)
- `auth_provider` — `local` or `google`
- `codigo_promo_activo` — Boolean; true if promo code is active
- `codigo_promo_hasta` — Date; expiration of active promo

**Common queries:**

```php
// Get vehicle with photo count
$vehiculo = fetchOne(
    "SELECT v.*, COUNT(f.id) as total_fotos FROM " . table('vehiculos') . " v
     LEFT JOIN " . table('vehiculo_fotos') . " f ON v.id = f.vehiculo_id
     WHERE v.id = ? AND v.estado = 'activo' GROUP BY v.id",
    [$id]
);

// Check if user is admin
if (esAdmin()) {
    // Only email joelchala07@gmail.com
}

// Get user's active listings
$mis_vehiculos = fetchAll(
    "SELECT * FROM " . table('vehiculos') . " WHERE usuario_id = ? AND estado IN ('activo','pendiente') ORDER BY created_at DESC",
    [$_SESSION['usuario_id']]
);

// Queue an email (sent asynchronously by cron every 5 min)
queueEmail(
    toEmail: 'user@example.com',
    toName: 'Juan García',
    subject: 'Tu vehículo ha recibido un contacto',
    bodyHtml: emailTemplate('Nuevo contacto', 'Alguien está interesado en tu vehículo'),
    metadata: ['vehiculo_id' => 123, 'evento' => 'nuevo_contacto']
);
```

---

## 🔐 Security Checklist

When adding features:

- [ ] Use prepared statements (never concatenate SQL)
- [ ] Sanitize HTML output with `htmlspecialchars()`
- [ ] Validate & limit file uploads (type, size, extension)
- [ ] Check `$_SESSION['usuario_id']` for auth-required pages
- [ ] Implement CSRF token verification on all POST/PUT/DELETE forms
- [ ] Whitelist external API responses before storing
- [ ] Log security events (login attempts, failed validations, etc.)
- [ ] Never log sensitive data (passwords, API keys, tokens)

---

## 🚀 Common Tasks

### Add a New Page

1. Create `public_html/public/my-page.php`
2. Include standard headers in the correct order:
   ```php
   <?php
   defined('MOTO_SPOT') || define('MOTO_SPOT', true);
   require_once __DIR__ . '/../includes/env.php';
   loadEnv();
   require_once __DIR__ . '/../includes/db.php';
   require_once __DIR__ . '/../includes/config.php';
   require_once __DIR__ . '/../includes/functions.php';
   require_once __DIR__ . '/../includes/auth.php';
   // Include more as needed (mailer, logger, stock_media, etc.)
   require_once __DIR__ . '/../includes/header.php';
   require_once __DIR__ . '/../includes/navbar.php';
   
   // Protect page if needed
   requerirAutenticacion(); // Redirect to login if not authenticated
   ```
3. `.htaccess` will auto-route `/my-page.php` to your file (rewrite rules configured)
4. Include footer before closing:
   ```php
   include __DIR__ . '/../includes/footer.php';
   ```

### Query the Database

```php
// Single result
$user = fetchOne("SELECT * FROM " . table('usuarios') . " WHERE email = ?", [$email]);

// Multiple results with pagination
$limit = 20;
$offset = ($page - 1) * $limit;
$vehicles = fetchAll(
    "SELECT * FROM " . table('vehiculos') . " WHERE estado = ? ORDER BY created_at DESC LIMIT ?, ?",
    ['activo', $offset, $limit]
);

// Execute non-SELECT (INSERT/UPDATE/DELETE) — returns affected rows
$success = query("UPDATE " . table('usuarios') . " SET plan = ? WHERE id = ?", ['premium', $id]);
```

### Handle File Uploads (Secure)

```php
// Validate and save upload
if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Error en la carga del archivo';
    exit;
}

$file = $_FILES['imagen'];
$max_size = 5 * 1024 * 1024; // 5 MB

if ($file['size'] > $max_size) {
    logger('warning', 'Upload rejected: file too large', ['size' => $file['size'], 'user_id' => $_SESSION['usuario_id']]);
    $_SESSION['error'] = 'La imagen no puede superar 5MB';
    exit;
}

// Validate extension
$allowed = ['jpg', 'jpeg', 'png', 'webp'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    $_SESSION['error'] = 'Formato no permitido. Use JPG, PNG o WebP';
    exit;
}

// Move to secure location (outside public_html)
$upload_dir = env('UPLOAD_PATH') . '/vehiculos/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
$filename = uniqid() . '_' . time() . '.' . $ext;
$filepath = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Save reference in DB
    query(
        "INSERT INTO " . table('vehiculo_fotos') . " (vehiculo_id, ruta, creado_en) VALUES (?, ?, NOW())",
        [$vehiculo_id, $filename]
    );
    logger('info', 'Image uploaded', ['vehiculo_id' => $vehiculo_id, 'file' => $filename]);
} else {
    logger('error', 'Upload failed', ['file' => $file['name'], 'user_id' => $_SESSION['usuario_id']]);
    $_SESSION['error'] = 'Error al guardar la imagen';
}
```

### Send an Email (Async Queue)

```php
// Queue email — actually sent by cron every 5 minutes
queueEmail(
    toEmail: 'buyer@example.com',
    toName: 'Juan García',
    subject: 'Tu vehículo ha recibido un contacto',
    bodyHtml: emailTemplate(
        titulo: 'Nuevo contacto interesado',
        contenido: 'Se ha registrado un nuevo contacto en tu anuncio de Toyota Corolla 2020.',
        cta: 'Ver detalles',
        ctaUrl: 'https://php.autolatino.site/mis-publicaciones.php'
    ),
    metadata: ['vehiculo_id' => 123, 'tipo_evento' => 'nuevo_contacto', 'contact_id' => 45]
);

// In admin panel, you can monitor email queue status in ms_email_queue table
// Retries are automatic; check 'status' and 'next_retry_at' columns
```

### Create a Cron Job

1. Create `cron/my-job.php` with standard includes + CLI check:
   ```php
   <?php
   if (php_sapi_name() !== 'cli') {
       die('CLI only — no web access');
   }
   
   defined('MOTO_SPOT') || define('MOTO_SPOT', true);
   require_once __DIR__ . '/../.motospot/.env';
   loadEnv();
   require_once __DIR__ . '/../includes/db.php';
   require_once __DIR__ . '/../includes/logger.php';
   
   logger('info', 'Cron job: my-job started');
   
   // Your logic here
   
   logger('info', 'Cron job: my-job completed');
   ```
2. Add cron expression in Hostinger hPanel → Cron Jobs
   - `*/5 * * * *` — Every 5 minutes
   - `0 3 * * *` — Daily at 3:00 AM
   - `0 4 * * 7` — Sundays at 4:00 AM (use for logs rotation, cleanup)
3. Point cron to: `/usr/bin/php /home/u986675534/domains/php.autolatino.site/public_html/cron/my-job.php`

### Implement Plan-Based Access

```php
// Check if user has paid plan (not free)
$usuario = getUsuarioActual();
$has_premium = isset($usuario['codigo_promo_activo']) && $usuario['codigo_promo_activo'] === 1;

if (!$has_premium) {
    $_SESSION['error'] = 'Esta función requiere un plan premium';
    redirect('/planes.php');
}

// Check specific plan
$plan_requerido = 'premium_plus';
if ($usuario['plan'] !== $plan_requerido) {
    $_SESSION['error'] = 'Requiere plan ' . $plan_requerido;
    redirect('/planes.php?plan=' . $plan_requerido);
}
```

### Redeem a Promo Code

```php
// See includes/codigos_promocionales.php for the function
$resultado = canjearCodigoPromocional(
    codigo: 'PROMO2026',
    usuario_id: $_SESSION['usuario_id']
);

if ($resultado['success']) {
    $_SESSION['success'] = 'Código canjeado. Plan activo hasta: ' . $resultado['fecha_fin'];
} else {
    $_SESSION['error'] = $resultado['message'];
}
```

---

## 📝 Conventions NOT in Use

- No framework (no Laravel, Symfony, WordPress)
- No build system (no webpack, Vite)
- No package manager (no Composer — dependencies are inline)
- No ORM (direct PDO + prepared statements)
- No unit tests (manual testing only)
- No type hints (PHP 8.3 supports them, but not enforced here)

---

## 🔗 Important Links

- **Production URL:** https://php.autolatino.site
- **Health check:** https://php.autolatino.site/health.php?token=ms_check_2026
- **Server:** Hostinger server547 (Asia/Singapore)
- **IP:** 151.106.116.61
- **SSH:** Port 65002, user `u986675534`
- **Admin:** `admin` / `joelchala07@gmail.com`

---

## 📖 Further Reading

- See `Documentacion del proyecto/README.md` for deployment & credentials
- See `REPORTE_BUGS_MOTOSPOT.md` for known issues
- See `Documentacion del proyecto/Caracteristicas del proyecto.md` for feature specs & table details
