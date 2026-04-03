<?php
/**
 * MotoSpot - Archivo de entrada principal
 * Redirige al directorio public/
 */

// Redirigir al directorio public
define('MOTO_SPOT', true);
require_once __DIR__ . '/public/index.php';