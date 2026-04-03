<?php
/**
 * MotoSpot - Header
 * Encabezado común de todas las páginas - Dark Mode
 * 
 * @author Kevin
 * @version 2.0.0
 */

// Incluir autenticación
require_once __DIR__ . '/auth.php';

// Obtener configuración del sitio
$siteName = 'MotoSpot';
$siteDescription = 'La plataforma líder para compra y venta de vehículos en Argentina';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($siteDescription); ?>">
    <meta name="keywords" content="autos, vehiculos, venta de autos, carros, argentina, motos, embarcaciones">
    <meta name="author" content="MotoSpot">
    
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' . $siteName : $siteName; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    
    <!-- CSS Principal -->
    <link rel="stylesheet" href="/assets/css/estilos.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <?php if (isset($extraCSS)): ?>
        <?php echo $extraCSS; ?>
    <?php endif; ?>
</head>
<body>
    <div class="page-wrapper">
