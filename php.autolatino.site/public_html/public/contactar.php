<?php
/**
 * MotoSpot - Enviar Mensaje al Vendedor
 * Procesa el formulario de contacto de detalle-vehiculo.php
 */

define('MOTO_SPOT', true);
require_once __DIR__ . '/../includes/auth.php';

// Solo acepta POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /listado-vehiculos.php');
    exit();
}

// Verificar CSRF token
$csrf_token = $_POST['csrf_token'] ?? '';
if (!verificarCSRFToken($csrf_token)) {
    header('Location: /listado-vehiculos.php');
    exit();
}

$vehiculoId = intval($_POST['vehiculo_id'] ?? 0);
if (!$vehiculoId) {
    header('Location: /listado-vehiculos.php');
    exit();
}

$mensaje   = trim($_POST['mensaje'] ?? '');
$nombre    = trim($_POST['nombre']  ?? '');
$email     = trim($_POST['email']   ?? '');
$telefono  = trim($_POST['telefono'] ?? '');

if (!validarString($mensaje, 10, 2000)) {
    header("Location: /detalle-vehiculo.php?id=$vehiculoId&error=mensaje_vacio");
    exit();
}

// Si el usuario está autenticado, usar sus datos de sesión
if (estaAutenticado()) {
    $usuario = getUsuarioActual();
    $nombre  = $usuario['nombre'] . ' ' . ($usuario['apellido'] ?? '');
    $email   = $usuario['email'];
    $telefono = $usuario['telefono'] ?? '';
}

// Obtener datos del vehículo y vendedor
$vehiculo = fetchOne(
    "SELECT v.id, v.titulo, u.email as vendedor_email, u.nombre as vendedor_nombre
     FROM ms_vehiculos v
     JOIN ms_usuarios u ON v.usuario_id = u.id
     WHERE v.id = ? AND v.estado_publicacion = 'activo'",
    [$vehiculoId]
);

if (!$vehiculo) {
    header('Location: /listado-vehiculos.php');
    exit();
}

// Guardar mensaje en la base de datos
try {
    executeQuery(
        "INSERT INTO ms_mensajes (vehiculo_id, remitente_nombre, remitente_email, remitente_telefono, mensaje, fecha_envio)
         VALUES (?, ?, ?, ?, ?, NOW())",
        [$vehiculoId, $nombre, $email, $telefono, $mensaje]
    );
} catch (Exception $e) {
    error_log("[MotoSpot] Error al guardar mensaje: " . $e->getMessage());
    header("Location: /detalle-vehiculo.php?id=$vehiculoId&error=envio_fallido");
    exit();
}

// Redirigir con confirmación
header("Location: /detalle-vehiculo.php?id=$vehiculoId&msg=mensaje_enviado");
exit();
?>
