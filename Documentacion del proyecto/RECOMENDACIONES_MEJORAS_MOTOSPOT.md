# 📋 RECOMENDACIONES DE MEJORA - MOTOSPOT

**Fecha:** 03 de Abril, 2026  
**Proyecto:** MotoSpot - Marketplace de Vehículos  
**Tipo:** Recomendaciones Estratégicas y Nuevas Funcionalidades  
**Objetivo:** Definir roadmap de funcionalidades futuras para crecimiento del negocio

---

## ⚠️ NOTA IMPORTANTE: CORRECCIÓN DE BUGS PREVIA

**ANTES de implementar cualquier mejora de este documento, es OBLIGATORIO corregir los bugs identificados en el reporte `REPORTE_BUGS_MOTOSPOT.md`:**

### 🔴 Bugs Críticos que deben corregirse primero:
1. **includes/stock_media.php** - Función `env()` no definida
2. **includes/auth.php** - Funciones `fetchOne()` y `executeQuery()` no definidas  
3. **listado-vehiculos.php** - Función `fetchOne()` no definida
4. **login.php** - Uso de `str_starts_with()` requiere PHP 8+

### Estado actual del proyecto:
- ❌ **NO** está listo para nuevas funcionalidades hasta corregir bugs
- ✅ Este documento es el **ROADMAP FUTURO** para después de estabilizar
- 📅 **Timeline sugerido:** 1-2 semanas para fixes, luego empezar mejoras

---

## 🎨 1. MEJORAS VISUALES Y UI/UX

### 1.1 Sistema de Diseño Consistente
**Prioridad:** ALTA  
**Descripción:** Implementar un Design System completo
- **Paleta de colores definida:** Documentar todos los colores con variables CSS
- **Tipografía escalable:** Sistema de fuentes con jerarquía clara
- **Componentes reutilizables:** Botones, cards, formularios, modales estandarizados
- **Espaciado consistente:** Sistema de grid y márgenes uniforme
- **Dark/Light mode:** Considerar tema claro opcional para usuarios

**Beneficio:** Uniformidad visual, desarrollo más rápido, mejor mantenimiento

---

### 1.2 Micro-interacciones y Animaciones
**Prioridad:** MEDIA  
**Implementaciones sugeridas:**
- **Skeleton screens:** Mientras cargan imágenes y datos
- **Hover effects:** Cards de vehículos con elevación suave
- **Loading states:** Spinners elegantes en botones de submit
- **Toast notifications:** Sistema de notificaciones no intrusivo
- **Smooth scrolling:** Transiciones suaves entre secciones
- **Parallax sutil:** En secciones hero para profundidad visual

**Tecnología sugerida:** Framer Motion (React) o GSAP (vanilla JS)

---

### 1.3 Mejoras en la Galería de Fotos
**Prioridad:** ALTA  
**Funcionalidades:**
- **Lightbox ampliado:** Zoom, fullscreen, navegación táctil
- **Carrusel touch:** Swipe en móviles
- **Lazy loading progresivo:** Carga progresiva de alta resolución
- **Comparador de fotos:** Antes/después si aplica
- **Marca de agua:** Protección automática en fotos originales
- **360° view:** Para vehículos premium (futuro)

---

### 1.4 Rediseño del Buscador
**Prioridad:** ALTA  
**Mejoras:**
- **Búsqueda predictiva:** Autocomplete con sugerencias
- **Filtros visuales:** Sliders de precio con rango visual
- **Mapa interactivo:** Ubicación de vehículos (integración Google Maps)
- **Búsqueda por voz:** Para usuarios móviles
- **Búsqueda guardada:** Alertas cuando aparezcan vehículos nuevos
- **Historial de búsquedas:** Acceso rápido a búsquedas recientes

---

### 1.5 Mejoras Mobile-First
**Prioridad:** CRÍTICA  
**Optimizaciones:**
- **App-like experience:** PWA (Progressive Web App)
- **Bottom navigation:** Navegación accesible en móvil
- **Swipe gestures:** Guardar favoritos, compartir
- **Optimización de imágenes:** WebP format con fallback
- **Touch targets:** Mínimo 44x44px para todos los botones
- **Viewport optimizado:** Meta tags específicos para móviles

---

## 🚀 2. FUNCIONALIDADES ATRACTIVAS PARA CLIENTES

### 2.1 Sistema de Favoritos y Alertas
**Prioridad:** ALTA  
**Características:**
- **Guardar búsquedas:** Notificación cuando hay nuevos resultados
- **Favoritos con colecciones:** "Mis favoritos", "Comparando", "Para comprar"
- **Alertas de precio:** Notificación si baja el precio de un vehículo
- **Historial de vistas:** "Visto recientemente"
- **Compartir:** WhatsApp, email, redes sociales con preview enriquecido
- **PDF del vehículo:** Generar ficha técnica descargable

---

### 2.2 Sistema de Mensajería Integrada
**Prioridad:** ALTA  
**Funcionalidades:**
- **Chat en tiempo real:** WebSocket para comunicación instantánea
- **Plantillas de mensajes:** "¿Sigue disponible?", "¿Acepta permuta?"
- **Adjuntos:** Enviar fotos adicionales por chat
- **Videollamadas:** Para mostrar el vehículo (WebRTC)
- **Traducción automática:** Si hay compradores internacionales
- **Moderación:** Filtro de spam y lenguaje inapropiado
- **Notificaciones push:** Cuando llegan mensajes nuevos

---

### 2.3 Simulador de Financiamiento
**Prioridad:** MEDIA  
**Calculadora interactiva:**
- **Cuotas mensuales:** Slider de enganche y plazos
- **Comparador de tasas:** Diferentes bancos/entidades
- **Pre-aprobación:** Formulario para solicitar crédito
- **Cotización en PDF:** Documento formal de la simulación
- **Integración bancaria:** API con entidades financieras (futuro)

---

### 2.4 Sistema de Valoración de Vehículos
**Prioridad:** MEDIA  
**Herramienta de tasación:**
- **Inteligencia artificial:** Estimar valor de mercado
- **Historial de precios:** Gráfico de evolución del valor
- **Comparador:** Vehículos similares y sus precios
- **Informe de valoración:** Documento oficial de cotización
- **Booking para tasación:** Agendar visita de tasador profesional

---

### 2.5 Verificación y Certificación
**Prioridad:** MEDIA  
**Programa de verificación:**
- **Inspección mecánica:** Checklist de 100 puntos
- **Verificación de documentos:** Validación de papeles
- **Badge "Verificado":** Insignia especial en publicaciones
- **Reporte de historial:** Accidentes, servicios, dueños previos
- **Garantía extendida:** Opción de comprar garantía adicional

---

### 2.6 Sistema de Subastas
**Prioridad:** BAJA (futuro)  
**Modalidad de subasta:**
- **Subastas en vivo:** Tiempo real con bids
- **Subastas a ciegas:** Mejor oferta gana
- **Reserva mínima:** Precio base configurable
- **Extensiones automáticas:** Si hay bids en últimos segundos
- **Transparencia:** Historial de ofertas visible

---

## 💼 3. MEJORAS PARA AGENCIAS Y DEALERS

### 3.1 Panel de Administración Avanzado
**Prioridad:** ALTA  
**Dashboard profesional:**
- **Métricas en tiempo real:** Vistas, contactos, conversiones
- **Gráficos de rendimiento:** Por período, por vehículo
- **Comparativas:** Rendimiento vs competencia
- **Exportación de datos:** Excel, PDF de estadísticas
- **Integración CRM:** Conectar con Salesforce, HubSpot, etc.
- **API pública:** Para integraciones externas

---

### 3.2 Gestión de Inventario Masivo
**Prioridad:** ALTA  
**Herramientas de gestión:**
- **Importación CSV/Excel:** Carga masiva de vehículos
- **Plantillas de publicación:** Formatos predefinidos
- **Duplicar publicación:** Clonar vehículos similares
- **Publicación programada:** Agendar cuándo aparece
- **Destacados automáticos:** Rotación de vehículos destacados
- **Alertas de stock:** Notificación cuando hay pocos vehículos

---

### 3.3 Sistema de Leads y CRM
**Prioridad:** ALTA  
**Gestión de clientes potenciales:**
- **Puntuación de leads:** Calificar interés del comprador
- **Seguimiento automático:** Recordatorios de contacto
- **Historial de interacciones:** Todas las comunicaciones
- **Etiquetas y segmentación:** Clasificar leads
- **Embudo de ventas:** Visualización del proceso de venta
- **Integración WhatsApp Business:** API oficial de WhatsApp

---

### 3.4 Publicidad y Promociones
**Prioridad:** MEDIA  
**Herramientas de marketing:**
- **Anuncios patrocinados:** Sistema de ads dentro de la plataforma
- **Retargeting:** Recordar a visitantes del vehículo
- **Email marketing:** Plantillas y automatización
- **Campañas en redes:** Integración Facebook Ads, Google Ads
- **Códigos de descuento:** Sistema de cupones personalizados
- **Landing pages:** Páginas personalizadas para dealers

---

## 🔧 4. MEJORAS TÉCNICAS Y ARQUITECTURA

### 4.1 Optimización de Performance
**Prioridad:** CRÍTICA  
**Implementaciones:**
- **CDN global:** CloudFlare o AWS CloudFront
- **Compresión de assets:** Gzip, Brotli
- **Imágenes WebP:** Conversión automática con fallback
- **Lazy loading:** Carga diferida de imágenes y videos
- **Code splitting:** Cargar JS/CSS solo cuando se necesita
- **Service workers:** Cache de recursos estáticos
- **Minificación:** CSS y JS optimizados para producción

**Objetivo:** PageSpeed Insights > 90 en mobile y desktop

---

### 4.2 SEO Avanzado
**Prioridad:** ALTA  
**Optimizaciones:**
- **URLs semánticas:** `/autos/bmw/serie-3/2023/`
- **Schema.org markup:** Rich snippets para vehículos
- **Sitemap XML dinámico:** Generación automática
- **Meta tags dinámicos:** Open Graph, Twitter Cards
- **Breadcrumbs estructurados:** Navegación jerárquica
- **Canonical URLs:** Evitar contenido duplicado
- **AMP pages:** Versiones aceleradas para móvil

---

### 4.3 Sistema de Cache Multinivel
**Prioridad:** ALTA  
**Arquitectura de cache:**
- **Opcode cache:** OPcache para PHP
- **Object cache:** Redis o Memcached para datos
- **Page cache:** Varnish o Nginx FastCGI cache
- **Query cache:** MySQL query cache optimizado
- **Browser cache:** Headers de expiración apropiados
- **API cache:** Cache de respuestas de APIs externas

---

### 4.4 Arquitectura Escalable
**Prioridad:** MEDIA (futuro)  
**Preparación para crecimiento:**
- **Load balancer:** Distribución de carga entre servidores
- **Base de datos replicada:** Master-slave para lecturas
- **Microservicios:** Separar componentes críticos
- **Cola de procesos:** RabbitMQ para tareas asíncronas
- **Almacenamiento distribuido:** AWS S3 o similar
- **Auto-scaling:** Escalado automático según demanda

---

### 4.5 Testing y CI/CD
**Prioridad:** MEDIA  
**Desarrollo profesional:**
- **Unit testing:** PHPUnit para lógica de negocio
- **Integration testing:** Pruebas de flujos completos
- **E2E testing:** Cypress o Playwright
- **CI/CD pipeline:** GitHub Actions o GitLab CI
- **Code review obligatorio:** Protección de ramas
- **SonarQube:** Análisis estático de código
- **Pre-commit hooks:** Formateo automático con PHP-CS-Fixer

---

## 🔒 5. SEGURIDAD Y PRIVACIDAD

### 5.1 Fortalecimiento de Seguridad
**Prioridad:** CRÍTICA  
**Medidas:**
- **WAF (Web Application Firewall):** Protección contra ataques comunes
- **Rate limiting:** Limitar intentos de login y acciones
- **Two-Factor Authentication (2FA):** Autenticación de doble factor
- **Captcha avanzado:** reCAPTCHA v3 invisible
- **Content Security Policy (CSP):** Headers de seguridad
- **Security headers:** HSTS, X-Frame-Options, X-XSS-Protection
- **Cifrado de datos sensibles:** Encriptación en BD si aplica

---

### 5.2 Cumplimiento GDPR/LGPD
**Prioridad:** ALTA  
**Requisitos legales:**
- **Consentimiento explícito:** Checkbox claro para términos
- **Política de privacidad detallada:** Uso de datos específico
- **Derecho al olvido:** Eliminar cuenta y datos completamente
- **Portabilidad de datos:** Exportar datos personales (JSON)
- **Cookie banner:** Gestión granular de cookies
- **Notificación de brechas:** Procedimiento ante fugas de datos
- **DPO (Data Protection Officer):** Designar responsable

---

## 📱 6. FUNCIONALIDADES MOBILE Y APP

### 6.1 Progressive Web App (PWA)
**Prioridad:** ALTA  
**Características:**
- **Instalación en home screen:** Icono como app nativa
- **Funcionamiento offline:** Ver vehículos guardados sin internet
- **Push notifications:** Notificaciones nativas del sistema
- **Background sync:** Sincronizar acciones cuando haya conexión
- **Splash screen:** Pantalla de carga personalizada
- **Modo standalone:** Sin barra de navegador

---

### 6.2 Aplicación Nativa (Futuro)
**Prioridad:** BAJA  
**Opciones:**
- **React Native:** Una codebase para iOS y Android
- **Flutter:** Alternativa de Google
- **Funcionalidades exclusivas:**
  - Cámara para fotos directas
  - Geolocalización precisa
  - Notificaciones push nativas
  - Integración con contactos

---

## 📊 7. ANALÍTICA Y DATOS

### 7.1 Dashboard de Analytics
**Prioridad:** MEDIA  
**Métricas clave:**
- **Google Analytics 4:** Implementación completa
- **Event tracking:** Cada acción importante
- **Funnel de conversión:** Desde vista hasta contacto
- **Heatmaps:** Hotjar o Microsoft Clarity
- **A/B testing:** Google Optimize o Optimizely
- **User journey:** Mapeo completo del recorrido
- **Cohort analysis:** Retención de usuarios

---

### 7.2 Business Intelligence
**Prioridad:** BAJA (futuro)  
**Análisis avanzado:**
- **Tendencias de mercado:** Precios por región/tiempo
- **Demanda predictiva:** Qué vehículos se buscarán
- **Precios óptimos:** Recomendación de precio de venta
- **Estacionalidad:** Mejores épocas para vender
- **Competencia:** Análisis de otros marketplaces
- **Reportes automáticos:** Envío semanal por email

---

## 🤖 8. INTELIGENCIA ARTIFICIAL Y AUTOMATIZACIÓN

### 8.1 IA para Mejoras de UX
**Prioridad:** MEDIA  
**Implementaciones:**
- **Recomendaciones personalizadas:** "También te puede interesar"
- **Búsqueda semántica:** "SUV familiar económico" → resultados relevantes
- **Chatbot inteligente:** Respuestas automáticas 24/7
- **Detección de fraudes:** Identificar publicaciones sospechosas
- **Moderación automática:** Revisión de fotos y descripciones
- **Precios sugeridos:** IA recomienda precio de venta

---

### 8.2 Automatización de Marketing
**Prioridad:** MEDIA  
**Workflows automatizados:**
- **Email drip campaigns:** Secuencia de emails post-registro
- **Re-engagement:** Recuperar usuarios inactivos
- **Abandoned search:** Recordar búsquedas no completadas
- **Birthday/anniversary:** Emails personalizados
- **Nuevos vehículos:** Alertas de novedades según preferencias

---

## 🌍 9. INTERNACIONALIZACIÓN Y EXPANSIÓN

### 9.1 Multi-país
**Prioridad:** BAJA (futuro)  
**Preparación:**
- **Multi-moneda:** USD, EUR, ARS, etc.
- **Multi-idioma:** Español, inglés, portugués
- **Dominios por país:** motospot.ar, motospot.mx, motospot.cl
- **Regulaciones locales:** Adaptarse a cada país
- **Métodos de pago locales:** MercadoPago, Stripe, etc.

---

### 9.2 Categorías Expandibles
**Prioridad:** MEDIA  
**Nuevas categorías:**
- **Vehículos industriales:** Camiones, maquinaria
- **Motos especiales:** Deportivas, touring
- **Bicicletas eléctricas:** E-bikes
- **Piezas y repuestos:** Marketplace de autopartes
- **Servicios:** Talleres, aseguradoras, gestorías
- **Accesorios:** Equipamiento, audio, etc.

---

## 💰 10. MONETIZACIÓN Y NEGOCIO

### 10.1 Modelos de Monetización Adicionales
**Prioridad:** MEDIA  
**Opciones:**
- **Comisión por venta:** % del valor de transacción exitosa
- **Listado destacado:** Pago por posiciones premium
- **Suscripción premium:** Para compradores (acceso anticipado)
- **Servicios financieros:** Créditos, seguros (afiliación)
- **Verificación paga:** Inspección mecánica premium
- **Publicidad de terceros:** Espacios para bancos, aseguradoras

---

### 10.2 Programa de Referidos
**Prioridad:** MEDIA  
**Sistema de referidos:**
- **Código único por usuario:** Compartir y ganar
- **Recompensas:** Créditos, meses gratis, destacados
- **Tracking completo:** Desde referido hasta conversión
- **Gamificación:** Badges por referidos exitosos
- **Bonos escalonados:** Más referidos = mejores recompensas

---

## 🛠️ 11. MEJORAS DE CONTENIDO

### 11.1 Blog y Contenido Educativo
**Prioridad:** MEDIA  
**Sección de contenidos:**
- **Blog de automóviles:** Noticias, reviews, consejos
- **Guías de compra:** "Cómo elegir tu primer auto"
- **Videos tutoriales:** Canal de YouTube integrado
- **Podcast:** Entrevistas a expertos
- **Infografías:** Datos del mercado automotor
- **Reviews de usuarios:** Experiencias reales de compra

---

### 11.2 Comunidad
**Prioridad:** BAJA  
**Espacio social:**
- **Foros de discusión:** Por marca, modelo, intereses
- **Grupos de interés:** "Amantes de los clásicos", etc.
- **Eventos:** Meetups, exposiciones, track days
- **Clubs de marca:** Comunidades oficiales por marca
- **Marketplace de comunidad:** Venta entre miembros

---

## 📋 PRIORIZACIÓN DE IMPLEMENTACIÓN

### 🚨 FASE 0: ESTABILIZACIÓN CRÍTICA (Inmediato - 1-2 semanas)
**⚠️ ESTA FASE ES OBLIGATORIA ANTES DE CUALQUIER MEJORA**

#### Corrección de Bugs Críticos (desde REPORTE_BUGS_MOTOSPOT.md):
1. 🔴 **includes/stock_media.php** - Agregar `require_once __DIR__ . '/env.php'`
2. 🔴 **includes/auth.php** - Agregar `require_once __DIR__ . '/functions.php'` después de db.php
3. 🔴 **public/listado-vehiculos.php** - Verificar inclusión de functions.php
4. 🔴 **public/login.php** - Reemplazar `str_starts_with()` con función compatible PHP 7.x:
   ```php
   // Reemplazar:
   if (!str_starts_with($redirect, '/') || str_starts_with($redirect, '//'))
   // Por:
   if (strpos($redirect, '/') !== 0 || strpos($redirect, '//') === 0)
   ```
5. 🟡 **Pruebas completas** - Verificar que login, registro, listados y búsqueda funcionen
6. 🟡 **Revisión de errores moderados** - Validación CSRF, manejo de errores en APIs

**Criterio de salida:** Todas las funcionalidades críticas deben funcionar sin errores

---

### FASE 1: FUNDAMENTOS SÓLIDOS (Semanas 3-6)
**Base técnica para soportar nuevas funcionalidades**

1. 🎨 **Implementar sistema de diseño consistente**
   - Variables CSS centralizadas
   - Componentes UI estandarizados
   - Guía de estilo documentada

2. 📱 **Optimizaciones mobile-first esenciales**
   - Bottom navigation para móvil
   - Touch targets optimizados
   - Responsive completo

3. ⚡ **Performance básico**
   - Imágenes WebP con fallback
   - Lazy loading implementado
   - Minificación de assets

4. 🔒 **Seguridad básica**
   - Rate limiting en login
   - Headers de seguridad
   - Preparación para 2FA

5. 📈 **SEO básico**
   - Meta tags dinámicos
   - URLs semánticas
   - Sitemap XML

---

### FASE 2: VALOR PARA USUARIOS (Meses 2-3)
**Funcionalidades que mejoran la experiencia de compra/venta**

1. 💬 **Sistema de mensajería integrada**
   - Chat básico (no necesita WebSocket todavía)
   - Notificaciones por email
   - Historial de conversaciones

2. ❤️ **Sistema de favoritos y alertas**
   - Guardar vehículos favoritos
   - Alertas de precio
   - Búsquedas guardadas

3. 📊 **Dashboard básico para dealers**
   - Estadísticas simples
   - Gestión de publicaciones
   - Respuesta a mensajes

4. 🔍 **Mejoras en búsqueda**
   - Autocomplete básico
   - Filtros avanzados visuales
   - Guardar búsquedas

5. 📸 **Mejoras en galería de fotos**
   - Lightbox ampliado
   - Zoom en imágenes
   - Navegación táctil

---

### FASE 3: DIFERENCIADORES (Meses 4-6)
**Funcionalidades que destacan de la competencia**

1. 🤖 **IA y personalización**
   - Recomendaciones de vehículos
   - Búsqueda semántica
   - Chatbot básico

2. 💰 **Simulador de financiamiento**
   - Calculadora interactiva
   - Comparador de tasas
   - Cotización en PDF

3. 📱 **PWA completa**
   - Instalación en home screen
   - Funcionamiento offline básico
   - Push notifications

4. 🔔 **Sistema de notificaciones avanzado**
   - Push nativo
   - Email marketing básico
   - Alertas inteligentes

5. 💼 **CRM para dealers**
   - Gestión de leads
   - Seguimiento de oportunidades
   - Estadísticas avanzadas

---

### FASE 4: ESCALA Y MONETIZACIÓN (Meses 7-12)
**Crecimiento y nuevos ingresos**

1. 🌍 **Preparación multi-país**
   - Multi-moneda
   - Adaptación legal por país
   - Dominios locales

2. 🏗️ **Arquitectura escalable**
   - CDN global
   - Cache distribuido
   - Base de datos replicada

3. 📊 **Business Intelligence**
   - Dashboard de analytics
   - Reportes automáticos
   - Predicción de tendencias

4. 🤝 **Programa de referidos**
   - Sistema de códigos
   - Recompensas automáticas
   - Tracking completo

5. 💰 **Nuevas fuentes de ingreso**
   - Comisión por venta exitosa
   - Publicidad destacada
   - Servicios financieros (afiliación)
   - Verificación paga (inspección mecánica)

6. 🏍️ **Expansión de categorías**
   - Vehículos industriales
   - Autopartes
   - Servicios relacionados

---

## 🎯 MÉTRICAS DE ÉXITO

### KPIs a Monitorear:
- **Conversion rate:** % de visitantes que contactan vendedor
- **Tiempo en sitio:** Promedio de sesión
- **Bounce rate:** % que abandona sin interactuar
- **Listings activos:** Cantidad de vehículos publicados
- **Leads generados:** Contactos por mes
- **Transacciones exitosas:** Ventas concretadas
- **NPS (Net Promoter Score):** Satisfacción de usuarios
- **CAC (Customer Acquisition Cost):** Costo de adquisición
- **LTV (Lifetime Value):** Valor de cliente a largo plazo

---

## 💡 CONCLUSIÓN

### 🎯 Orden Correcto de Implementación:

**1️⃣ INMEDIATO (Esta semana):**
Corregir los 4 bugs críticos del reporte técnico. Sin esto, el sistema no funciona.

**2️⃣ FASE 0 (1-2 semanas):**
Estabilización completa. Todas las funcionalidades básicas deben funcionar sin errores.

**3️⃣ FASE 1-4 (3-12 meses):**
Implementar nuevas funcionalidades de este roadmap según prioridad de negocio.

### 📊 Estas recomendaciones están diseñadas para:

1. **Estabilizar la plataforma:** Corregir bugs antes de agregar complejidad
2. **Mejorar la experiencia del usuario:** Hacer la compra/venta más fácil y segura
3. **Aumentar conversiones:** Más leads y transacciones exitosas
4. **Retener usuarios:** Crear valor a largo plazo
5. **Escalar el negocio:** Preparar la plataforma para crecimiento
6. **Diferenciarse de la competencia:** Ofrecer funcionalidades únicas

### ⚠️ Advertencias importantes:

- **NO** implementar mejoras hasta corregir bugs críticos
- **NO** agregar nuevas funcionalidades si las existentes fallan
- **SÍ** probar exhaustivamente después de cada fase
- **SÍ** mantener el sistema funcionando en todo momento

**Recomendación final:** 
- **Semana 1-2:** Solo corrección de bugs (REPORTE_BUGS_MOTOSPOT.md)
- **Semana 3+:** Comenzar con Fase 1 de este documento
- **Enfoque:** Calidad sobre cantidad - mejor pocas features bien hechas que muchas rotas

---

**Documento creado por:** Equipo de Desarrollo  
**Fecha de creación:** 2026-04-03  
**Próxima revisión recomendada:** 2026-05-03
