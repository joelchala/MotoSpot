# REPORTE UX Y EXPERIENCIA DE USUARIO — MotoSpot

**Fecha:** 2026-04-04  
**Stack:** HTML5, CSS3 (Vanilla), JavaScript (Vanilla), AOS.js, Font Awesome  
**Diseño:** Dark theme, responsive, mobile-first

---

## RESUMEN

| Categoría | Issues | Prioridad |
|-----------|--------|-----------|
| Responsividad | 6 | Alto |
| Accesibilidad | 5 | Alto |
| Funcionalidad Rota | 5 | Crítico |
| Consistencia Visual | 4 | Medio |
| Navegación | 3 | Medio |
| **TOTAL** | **23** | — |

---

## FUNCIONALIDAD ROTA

### UX-01: Búsqueda del Hero No Funciona [CRÍTICO]
**Archivo:** `public/index.php:201` (campo `q`), `public/listado-vehiculos.php`  
El campo de búsqueda del hero envía `?q=...&tipo=...` pero `listado-vehiculos.php` nunca lee `$_GET['q']`. La búsqueda principal del sitio no funciona.

**Corrección:** Agregar en `listado-vehiculos.php`:
```php
if (!empty($_GET['q'])) {
    $where[] = "(v.titulo LIKE ? OR v.marca LIKE ? OR v.modelo LIKE ?)";
    $params[] = "%{$_GET['q']}%";
    $params[] = "%{$_GET['q']}%";
    $params[] = "%{$_GET['q']}%";
}
```

---

### UX-02: Ordenamiento de Resultados No Funciona [CRÍTICO]
**Archivo:** `public/listado-vehiculos.php:298-304` (select de orden), backend  
El dropdown de ordenamiento agrega `?sort=precio_asc` a la URL pero el backend nunca lee `$_GET['sort']`. El ORDER BY siempre es `v.destacado DESC, v.fecha_publicacion DESC`.

---

### UX-03: Filtros Activos Muestra Solo 3 de 11 [ALTO]
**Archivo:** `public/listado-vehiculos.php:309-326`  
Solo se muestran tags de filtros para marca, tipo y condición. Faltan: modelo, año, precio, transmisión, combustible, ciudad, destacados. El usuario no puede ver ni quitar filtros aplicados.

---

### UX-04: Funciones de Validación No Existen [CRÍTICO]
**Archivo:** `includes/functions.php`  
`validarURL()`, `validarPasswordSegura()`, `validarTelefono()` están después de `?>` y no se ejecutan. Cualquier página que las llame produce Fatal Error.

---

### UX-05: reset-password.php No Renderiza en GET [CRÍTICO]
**Archivo:** `public/reset-password.php:50-92`  
Falta llave de cierre del bloque POST. La página no muestra formulario cuando el token es válido (el include de header/footer queda dentro del POST).

---

## RESPONSIVIDAD

### UX-06: Sidebar de Filtros 320px Fijo Rompe en Móvil [ALTO]
**Archivo:** `estilos.css`  
`.listing-filters { width: 320px; }` en pantallas < 340px causa scroll horizontal.

**Corrección:** `width: min(320px, calc(100vw - 20px))`

---

### UX-07: Sidebar de Detalle 380px Estrecho en Tablets [MEDIO]
**Archivo:** `estilos.css`  
En pantallas 992-1100px, el sidebar de 380px deja poco espacio para el contenido principal.

---

### UX-08: Breakpoint Gap 768-992px [MEDIO]
No hay breakpoint intermedio para tablets. Salto directo de móvil a desktop.

---

### UX-09: Footer 4 Columnas Sin Breakpoint [MEDIO]
**Archivo:** `landing-modern.css`  
Footer grid de 4 columnas no tiene media query para pantallas ~1100px.

---

### UX-10: Toast 300px Rompe en Pantallas Pequeñas [MEDIO]
**Archivo:** `estilos.css`  
`min-width: 300px` en toast excede pantallas < 340px.

---

### UX-11: Video Modal No Bloquea Scroll del Body [BAJO]
Al abrir el modal de video, el body sigue scrolleable.

---

## ACCESIBILIDAD

### UX-12: Touch Targets < 44px [ALTO]
**Archivos:** `estilos.css` — `.filter-tag`, `.page-link`  
WCAG 2.1 requiere mín. 44×44px para targets táctiles.

---

### UX-13: Sin prefers-reduced-motion [ALTO]
No hay `@media (prefers-reduced-motion: reduce)` para deshabilitar animaciones AOS.

---

### UX-14: Focus Outline Eliminado Globalmente [ALTO]
**Archivo:** `estilos.css`  
`*:focus { outline: none; }` elimina indicadores de foco para usuarios de teclado.

**Corrección:** Usar `:focus-visible` en lugar de `:focus`.

---

### UX-15: Imágenes Sin Atributos Width/Height [MEDIO]
Causa Cumulative Layout Shift (CLS) durante la carga.

---

### UX-16: Inputs Sin autocomplete [BAJO]
Campos de formulario no especifican `autocomplete` para facilitar autofill.

---

## NAVEGACIÓN

### UX-17: Mobile Menu Frágil [ALTO]
**Archivo:** `estilos.css` + `app.js`  
El menú depende de media queries sincronizados. En pantallas 769-991px el botón aparece pero el menú puede no funcionar correctamente.

---

### UX-18: Galería Sin Prev/Next [MEDIO]
**Archivo:** `public/detalle-vehiculo.php`  
Solo thumbnails. No hay botones de navegación sobre la imagen principal.

---

### UX-19: Galería Sin Swipe en Móvil [MEDIO]
No hay touch events para deslizar entre fotos.

---

## CONSISTENCIA VISUAL

### UX-20: Dos Sistemas de Diseño CSS Incompatibles [ALTO]
**Archivos:** `estilos.css` vs `landing-modern.css`

| Variable | estilos.css | landing-modern.css |
|----------|------------|-------------------|
| Spacing | `--spacing-4` | `--space-4` |
| Background | `--color-bg-*` | `--color-bg` |
| Border radius | `--border-radius` | `--radius` |

---

### UX-21: CSS Inline Masivo [MEDIO]
~300 líneas de CSS inline en `planes.php`, `admin-codigos.php`, `recuperar-password.php`, `reset-password.php`. No cacheable, difícil de mantener.

---

### UX-22: Hero Title Duplicado [BAJO]
Definición en `index.php` y en `estilos.css` con valores diferentes.

---

### UX-23: Formato de Fechas Inconsistente [BAJO]
Páginas usan `d/m/Y`, `M Y`, `F Y` indistintamente.

---

## RECOMENDACIONES UX

### Prioridad P0 (corregir esta semana):
1. Implementar búsqueda con parámetro `q` en listado
2. Implementar ordenamiento funcional
3. Corregir functions.php (código después de `?>`)
4. Corregir reset-password.php (llave faltante)
5. Mostrar todos los filtros activos con posibilidad de quitar

### Prioridad P1 (este mes):
6. Agregar touch targets de 44px mínimo
7. Implementar `prefers-reduced-motion`
8. Corregir focus-visible
9. Unificar sistema de variables CSS
10. Mover CSS inline a archivos externos

### Prioridad P2 (próximo trimestre):
11. Agregar navegación prev/next en galería
12. Implementar swipe en galería móvil
13. Agregar width/height a todas las imágenes
14. Agregar breakpoints intermedios para tablets
15. Implementar formato de fechas consistente

---

## AUDITORÍA DE ACCESIBILIDAD WCAG 2.1

| Criterio | Estado | Notas |
|----------|--------|-------|
| 1.1.1 Non-text Content | ⚠️ | Alt text presente pero no verificado |
| 1.3.1 Info and Relationships | ✅ | Semántica HTML correcta |
| 1.4.3 Contrast | ⚠️ | Algunos textos rgba(255,255,255,.5) pueden fallar |
| 1.4.11 Non-text Contrast | ✅ | Bordes y controles visibles |
| 2.1.1 Keyboard | ❌ | Focus eliminado globalmente |
| 2.4.7 Focus Visible | ❌ | `outline: none` en `*:focus` |
| 2.5.5 Target Size | ❌ | Touch targets < 44px |
| 3.3.2 Labels | ✅ | Labels asociados a inputs |
| 4.1.2 Name, Role, Value | ✅ | Roles ARIA básicos presentes |

---

*Documento generado 2026-04-04.*
