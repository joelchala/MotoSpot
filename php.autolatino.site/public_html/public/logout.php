<?php
/**
 * MotoSpot - Logout
 * Cierre de sesión
 * 
 * @author Kevin
 * @version 1.0.0
 */

define('MOTO_SPOT', true);
require_once __DIR__ . '/../includes/auth.php';

// Cerrar sesión
logout();

// Redirigir al home
header('Location: /index.php');
exit();
?>