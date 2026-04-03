<?php
/**
 * MotoSpot - Módulo de Autenticación
 * Gestión de sesiones y autenticación de usuarios
 * 
 * @author Kevin
 * @version 1.0.0
 */

// Prevenir acceso directo
if (!defined('MOTO_SPOT')) {
    define('MOTO_SPOT', true);
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir conexión a base de datos
require_once __DIR__ . '/db.php';

// Incluir funciones auxiliares
require_once __DIR__ . '/functions.php';

/**
 * Verifica si el usuario está autenticado
 * @return bool
 */
function estaAutenticado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Verifica si el usuario es una agencia
 * @return bool
 */
function esAgencia() {
    return isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'agencia';
}

/**
 * Verifica si el usuario es administrador
 * @return bool
 */
function esAdmin() {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
}

/**
 * Obtiene el ID del usuario actual
 * @return int|null
 */
function getUsuarioId() {
    return $_SESSION['usuario_id'] ?? null;
}

/**
 * Obtiene los datos del usuario actual
 * @return array|null
 */
function getUsuarioActual() {
    if (!estaAutenticado()) {
        return null;
    }
    
    $sql = "SELECT * FROM ms_usuarios WHERE id = ? AND estado = 'activo'";
    return fetchOne($sql, [getUsuarioId()]);
}

/**
 * Inicia sesión de usuario
 * @param string $email
 * @param string $password
 * @return array [success => bool, message => string, user => array|null]
 */
function login($email, $password) {
    // Buscar usuario por email
    $sql = "SELECT * FROM ms_usuarios WHERE email = ? LIMIT 1";
    $usuario = fetchOne($sql, [$email]);
    
    if (!$usuario) {
        return [
            'success' => false,
            'message' => 'El correo electrónico no está registrado',
            'user' => null
        ];
    }
    
    // Verificar contraseña
    if (!password_verify($password, $usuario['password'])) {
        return [
            'success' => false,
            'message' => 'La contraseña es incorrecta',
            'user' => null
        ];
    }
    
    // Verificar estado de la cuenta
    if ($usuario['estado'] !== 'activo') {
        return [
            'success' => false,
            'message' => 'Tu cuenta está ' . $usuario['estado'] . '. Contacta soporte.',
            'user' => null
        ];
    }
    
    // Crear sesión
    crearSesionUsuario($usuario);
    
    // Actualizar último acceso
    $sql = "UPDATE ms_usuarios SET ultimo_acceso = NOW() WHERE id = ?";
    executeQuery($sql, [$usuario['id']]);
    
    return [
        'success' => true,
        'message' => 'Inicio de sesión exitoso',
        'user' => $usuario
    ];
}

/**
 * Crea la sesión del usuario
 * @param array $usuario
 */
function crearSesionUsuario($usuario) {
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_tipo'] = $usuario['tipo'];
    $_SESSION['usuario_rol'] = $usuario['rol'] ?? 'usuario';
    $_SESSION['usuario_foto'] = $usuario['foto_perfil'];
    $_SESSION['usuario_plan'] = $usuario['plan'] ?? 'gratis';
    $_SESSION['usuario_plan_activo'] = $usuario['codigo_promo_activo'] ?? 0;
    $_SESSION['usuario_plan_hasta'] = $usuario['codigo_promo_hasta'] ?? null;
    $_SESSION['login_time'] = time();
}

/**
 * Registra un nuevo usuario
 * @param array $datos
 * @return array [success => bool, message => string, user_id => int|null]
 */
function registrarUsuario($datos) {
    // Verificar si el email ya existe
    $sql = "SELECT id FROM ms_usuarios WHERE email = ?";
    $existe = fetchOne($sql, [$datos['email']]);
    
    if ($existe) {
        return [
            'success' => false,
            'message' => 'Este correo electrónico ya está registrado',
            'user_id' => null
        ];
    }
    
    // Encriptar contraseña
    $passwordHash = password_hash($datos['password'], PASSWORD_BCRYPT);
    
    // Insertar usuario
    $sql = "INSERT INTO ms_usuarios (tipo, email, password, nombre, apellido, telefono,
            nombre_agencia, direccion, ciudad, provincia, horario_atencion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [
        $datos['tipo'],
        $datos['email'],
        $passwordHash,
        $datos['nombre'],
        $datos['apellido'] ?? null,
        $datos['telefono'],
        $datos['nombre_agencia'] ?? null,
        $datos['direccion'] ?? null,
        $datos['ciudad'] ?? null,
        $datos['provincia'] ?? null,
        $datos['horario_atencion'] ?? null
    ];
    
    try {
        $stmt = executeQuery($sql, $params);
        $userId = getLastInsertId();
        
        return [
            'success' => true,
            'message' => 'Usuario registrado exitosamente',
            'user_id' => $userId
        ];
    } catch (Exception $e) {
        error_log("Error al registrar usuario: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al registrar el usuario. Intente nuevamente.',
            'user_id' => null
        ];
    }
}

/**
 * Cierra la sesión del usuario
 */
function logout() {
    // Limpiar todas las variables de sesión
    $_SESSION = [];
    
    // Destruir la cookie de sesión de forma segura
    if (isset($_COOKIE[session_name()])) {
        setcookie(
            session_name(),
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => true,
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'samesite' => 'Strict'
            ]
        );
    }
    
    // Destruir la sesión
    session_destroy();
}

/**
 * Redirige si el usuario no está autenticado
 * @param string $redirectUrl URL a redirigir después del login
 */
function requerirAutenticacion($redirectUrl = '') {
    if (!estaAutenticado()) {
        $loginUrl = '/login.php';
        if (!empty($redirectUrl)) {
            $loginUrl .= '?redirect=' . urlencode($redirectUrl);
        }
        header("Location: $loginUrl");
        exit();
    }
}

/**
 * Redirige si el usuario está autenticado (para páginas de login/registro)
 * @param string $redirectUrl
 */
function redirigirSiAutenticado($redirectUrl = '/index.php') {
    if (estaAutenticado()) {
        header("Location: $redirectUrl");
        exit();
    }
}

/**
 * Genera un token CSRF
 * @return string
 */
function generarCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica el token CSRF
 * @param string $token
 * @return bool
 */
function verificarCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Regenera el ID de sesión por seguridad
 * Previene session fixation attacks
 */
function regenerarSesion() {
    // Regenerar ID evitando session fixation attacks
    session_regenerate_id(true);
    logger('info', 'Session ID regenerated for security', ['user_id' => $_SESSION['usuario_id'] ?? null]);
}
