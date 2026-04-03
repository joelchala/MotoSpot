<?php
/**
 * MotoSpot - Configuración del Sitio
 * Archivo generado automáticamente
 */

// Prevenir acceso directo
if (!defined('MOTO_SPOT')) {
    define('MOTO_SPOT', true);
}

return [
    // Información del Sitio
    'site_name' => 'MotoSpot',
    'site_description' => 'Compra y venta de vehículos',
    'site_url' => 'https://php.autolatino.site',
    'site_email' => 'info@php.autolatino.site',
    'admin_email' => 'joelchala07@gmail.com',
    
    // Configuración Regional
    'timezone' => 'America/Argentina/Buenos_Aires',
    'language' => 'es',
    'locale' => 'es_AR',
    'currency' => 'USD',
    'country' => 'Argentina',
    
    // Base de Datos
    'db_host' => 'srv547.hstgr.io',
    'db_name' => 'u986675534_moto',
    'db_user' => 'u986675534_spot',
    'db_pass' => 'AKKuDQ&l~9d',
    'db_prefix' => 'ms_',
    'db_charset' => 'utf8mb4',
    
    // Seguridad
    'installed' => true,
    'install_date' => '2026-03-28',
    'session_lifetime' => 7200,
    'cookie_secure' => false,
    'cookie_httponly' => true,
    
    // Funcionalidades
    'enable_blog' => true,
    'enable_reviews' => true,
    'enable_compare' => true,
    'enable_favorites' => true,
    'enable_sharing' => true,
    'enable_contact_form' => true,
    'enable_multilang' => true,
    'enable_newsletter' => false,
    'enable_auctions' => false,
    
    // Configuración de Listados
    'items_per_page' => 12,
    'max_fotos_vehiculo' => 10,
    'dias_vencimiento_gratis' => 30,
    
    // Opciones Avanzadas
    'maintenance_mode' => false,
    'registration_open' => true,
    'email_verification' => false,
    'auto_approve_listings' => true,
    'max_upload_size' => 5,
    'allowed_formats' => ['jpg', 'jpeg', 'png', 'gif'],
    
    // Diseño Home
    'home_design' => 'modern',
    'home_search' => true,
    'home_categories' => true,
    'home_stats' => true,
    'home_testimonials' => true,
    'home_brands' => true,
    'home_featured' => true,
    
    // Listados
    'listings_layout' => 'grid',
    'listings_filters' => true,
    'listings_map' => true,
    'listings_seller_info' => true,
    
    // Rendimiento
    'cache_enabled' => true,
    'gzip_compression' => true,
    'debug_mode' => false,
    'ssl_force' => false,
    
    // SMTP (configurar manualmente si es necesario)
    'smtp_host' => '',
    'smtp_port' => 587,
    'smtp_user' => '',
    'smtp_pass' => '',
    'smtp_encryption' => 'tls',
    
    // reCAPTCHA (configurar manualmente si es necesario)
    'recaptcha_site_key' => '',
    'recaptcha_secret_key' => '',
];