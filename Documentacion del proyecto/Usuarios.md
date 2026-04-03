# MotoSpot — Usuarios del Sistema

> ⚠️ Documento confidencial. No compartir ni subir al servidor.

---

## Usuarios Registrados en BD

### Usuario #1 — Administrador

| Campo | Valor |
|---|---|
| **ID** | 1 |
| **Nombre** | admin |
| **Apellido** | — |
| **Email** | joelchala07@gmail.com |
| **Contraseña (texto plano)** | Admin159 |
| **Contraseña (hash bcrypt)** | `$2y$10$8PNSPjOFL3ZqNZsxLJdCSuQPT2J.P6fXJ.XCqOJUJZPJVQ0E5K2LG` |
| **Tipo** | individual |
| **Estado** | activo |
| **Email verificado** | Sí |
| **Fecha de registro** | 2026-03-28 14:12:04 |

---

## Roles y Tipos del Sistema

### Tipos de usuario (`ms_usuarios.tipo`)

| Tipo | Descripción |
|---|---|
| `individual` | Vendedor particular — acceso estándar |
| `agencia` | Concesionario o agencia — puede tener nombre de empresa, RNC, horario |

> ⚠️ **Nota:** La tabla `ms_usuarios` no tiene columna `rol`. El acceso de administrador se maneja mediante la cuenta con email `joelchala07@gmail.com` directamente en el código (`esAdmin()` en `auth.php`). Si se necesita un sistema de roles (admin / moderador / usuario), se debe agregar la columna `rol` a la tabla.

### Estados de usuario (`ms_usuarios.estado`)

| Estado | Descripción |
|---|---|
| `activo` | Usuario activo, puede acceder y publicar |
| `inactivo` | Usuario desactivado temporalmente |
| `suspendido` | Usuario bloqueado por incumplimiento |

---

## Planes por usuario

Los planes se asignan según la tabla `ms_configuracion` o directamente en `ms_usuarios` (pendiente de implementar la columna `plan_activo`). Los planes disponibles son:

| Plan | Acceso |
|---|---|
| `gratis` | Por defecto al registrarse |
| `destacado` | Requiere pago — $9.99/mes |
| `premium` | Requiere pago — $24.99/mes |
| `premium_plus` | Requiere pago — $49.99/mes |

---

## Credenciales de Sistemas (no usuarios del sitio)

| Sistema | Usuario | Contraseña / Key |
|---|---|---|
| MySQL | `u986675534_spot` | `AKKuDQ&l~9d` |
| FTP | `u986675534.php.autolatino.site` | `AKKuDQ&l~9d` |
| SSH | `u986675534` | Clave: `C:\Users\joel.chala\.ssh\hostinger_ed25519` |

---

## Pendiente

- [ ] Agregar columna `rol` ENUM(`admin`,`moderador`,`usuario`) a `ms_usuarios`
- [ ] Agregar columna `plan_activo` y `plan_vence` a `ms_usuarios`
- [ ] Crear tabla `ms_planes_activos` para histórico de pagos y vencimientos
