<?php
/**
 * MotoSpot - Página de Perfil
 * Perfil de usuario y gestión de cuenta
 * 
 * @author Kevin
 * @version 1.0.0
 */

define('MOTO_SPOT', true);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Requerir autenticación
requerirAutenticacion('/perfil.php');

$usuario = getUsuarioActual();
if (!$usuario) {
    logout();
    header('Location: /login.php');
    exit();
}

$error = '';
$success = '';

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Verificar CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verificarCSRFToken($csrf_token)) {
        $error = 'Token de seguridad inválido. Por favor, intenta de nuevo.';
    } else if ($_POST['action'] === 'update_profile') {
        // Actualizar datos básicos
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $telefono_adicional = trim($_POST['telefono_adicional'] ?? '');

        // Campos de agencia
        $nombre_agencia = trim($_POST['nombre_agencia'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $ciudad = trim($_POST['ciudad'] ?? '');
        $provincia = trim($_POST['provincia'] ?? '');
        $horario_atencion = trim($_POST['horario_atencion'] ?? '');
        $descripcion_empresa = trim($_POST['descripcion_empresa'] ?? '');

        if (!validarString($nombre, 2, 100)) {
            $error = 'El nombre debe tener entre 2 y 100 caracteres';
        } elseif ($apellido !== '' && !validarString($apellido, 2, 100)) {
            $error = 'El apellido debe tener entre 2 y 100 caracteres';
        } elseif ($telefono !== '' && !validarTelefono($telefono)) {
            $error = 'El teléfono principal no tiene un formato válido';
        } elseif ($telefono_adicional !== '' && !validarTelefono($telefono_adicional)) {
            $error = 'El teléfono adicional no tiene un formato válido';
        } else {
            $sql = "UPDATE ms_usuarios SET
                    nombre = ?,
                    apellido = ?,
                    telefono = ?,
                    telefono_adicional = ?,
                    nombre_agencia = ?,
                    direccion = ?,
                    ciudad = ?,
                    provincia = ?,
                    horario_atencion = ?,
                    descripcion_empresa = ?
                    WHERE id = ?";
            
            $params = [
                $nombre, $apellido, $telefono, $telefono_adicional,
                $nombre_agencia, $direccion, $ciudad, $provincia,
                $horario_atencion, $descripcion_empresa, $usuario['id']
            ];
            
            if (executeQuery($sql, $params)) {
                $success = 'Perfil actualizado exitosamente';
                // Recargar datos del usuario
                $usuario = getUsuarioActual();
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
            } else {
                $error = 'Error al actualizar el perfil';
            }
        }
    }
    
    if ($_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        $pwValidation = validarPasswordSegura($new_password);
        if (!password_verify($current_password, $usuario['password'])) {
            $error = 'La contraseña actual es incorrecta';
        } elseif (!$pwValidation['valid']) {
            $error = $pwValidation['error'];
        } elseif ($new_password !== $confirm_password) {
            $error = 'Las contraseñas no coinciden';
        } else {
            $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
            $sql = "UPDATE ms_usuarios SET password = ? WHERE id = ?";
            if (executeQuery($sql, [$new_hash, $usuario['id']])) {
                $success = 'Contraseña actualizada exitosamente';
            } else {
                $error = 'Error al cambiar la contraseña';
            }
        }
    }
}

// Obtener estadísticas del usuario
$stats = [
    'publicaciones_activas' => fetchOne(
        "SELECT COUNT(*) as total FROM ms_vehiculos WHERE usuario_id = ? AND estado_publicacion = 'activo'",
        [$usuario['id']]
    )['total'] ?? 0,
    'publicaciones_total' => fetchOne(
        "SELECT COUNT(*) as total FROM ms_vehiculos WHERE usuario_id = ?",
        [$usuario['id']]
    )['total'] ?? 0,
    'vistas_totales' => fetchOne(
        "SELECT SUM(vistas) as total FROM ms_vehiculos WHERE usuario_id = ?",
        [$usuario['id']]
    )['total'] ?? 0
];

$pageTitle = 'Mi Perfil';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<section class="profile-section">
    <div class="container">
        <div class="profile-grid">
            <!-- Sidebar -->
            <aside class="profile-sidebar">
                <div class="profile-card profile-card-user">
                    <div class="profile-avatar">
                        <?php if ($usuario['foto_perfil']): ?>
                            <img src="<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Foto de perfil">
                        <?php else: ?>
                            <div class="avatar-default">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <button class="btn-avatar-edit" title="Cambiar foto">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <h2 class="profile-name">
                        <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>
                    </h2>
                    <p class="profile-type">
                        <?php if ($usuario['tipo'] === 'agencia'): ?>
                            <i class="fas fa-building"></i> Agencia
                        <?php else: ?>
                            <i class="fas fa-user"></i> Particular
                        <?php endif; ?>
                    </p>
                    <p class="profile-since">
                        Miembro desde <?php echo date('F Y', strtotime($usuario['fecha_registro'])); ?>
                    </p>
                </div>
                
                <div class="profile-card profile-card-stats">
                    <h3>Estadísticas</h3>
                    <div class="stats-list">
                        <div class="stat-row">
                            <span class="stat-label">Publicaciones activas</span>
                            <span class="stat-value"><?php echo $stats['publicaciones_activas']; ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Total publicaciones</span>
                            <span class="stat-value"><?php echo $stats['publicaciones_total']; ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Vistas totales</span>
                            <span class="stat-value"><?php echo number_format($stats['vistas_totales']); ?></span>
                        </div>
                    </div>
                </div>
                
                <nav class="profile-nav">
                    <a href="#info" class="profile-nav-item active">
                        <i class="fas fa-user-circle"></i> Información
                    </a>
                    <a href="/mis-publicaciones.php" class="profile-nav-item">
                        <i class="fas fa-car"></i> Mis Publicaciones
                    </a>
                    <a href="/publicar-vehiculo.php" class="profile-nav-item">
                        <i class="fas fa-plus-circle"></i> Publicar Vehículo
                    </a>
                    <a href="#seguridad" class="profile-nav-item">
                        <i class="fas fa-lock"></i> Seguridad
                    </a>
                    <a href="/logout.php" class="profile-nav-item profile-nav-item-danger">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                </nav>
            </aside>
            
            <!-- Main Content -->
            <main class="profile-main">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Información del Perfil -->
                <div class="profile-content-card" id="info">
                    <div class="card-header">
                        <h2><i class="fas fa-user-edit"></i> Información del Perfil</h2>
                    </div>
                    <div class="card-body">
                        <form action="/perfil.php" method="POST" class="profile-form">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="form-row">
                                <div class="form-group form-group-half">
                                    <label for="nombre">Nombre *</label>
                                    <input type="text" id="nombre" name="nombre" class="form-control"
                                           value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                                </div>
                                <div class="form-group form-group-half">
                                    <label for="apellido">Apellido</label>
                                    <input type="text" id="apellido" name="apellido" class="form-control"
                                           value="<?php echo htmlspecialchars($usuario['apellido']); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Correo Electrónico</label>
                                <input type="email" id="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled>
                                <small class="form-text">El correo electrónico no se puede cambiar</small>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group form-group-half">
                                    <label for="telefono">Teléfono *</label>
                                    <input type="tel" id="telefono" name="telefono" class="form-control"
                                           value="<?php echo htmlspecialchars($usuario['telefono']); ?>" required>
                                </div>
                                <div class="form-group form-group-half">
                                    <label for="telefono_adicional">Teléfono Adicional</label>
                                    <input type="tel" id="telefono_adicional" name="telefono_adicional" class="form-control"
                                           value="<?php echo htmlspecialchars($usuario['telefono_adicional'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <?php if ($usuario['tipo'] === 'agencia'): ?>
                                <hr class="form-divider">
                                <h3 class="form-section-title">Información de la Agencia</h3>
                                
                                <div class="form-group">
                                    <label for="nombre_agencia">Nombre de la Agencia</label>
                                    <input type="text" id="nombre_agencia" name="nombre_agencia" class="form-control"
                                           value="<?php echo htmlspecialchars($usuario['nombre_agencia'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="direccion">Dirección</label>
                                    <input type="text" id="direccion" name="direccion" class="form-control"
                                           value="<?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group form-group-half">
                                        <label for="ciudad">Ciudad</label>
                                        <input type="text" id="ciudad" name="ciudad" class="form-control"
                                               value="<?php echo htmlspecialchars($usuario['ciudad'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group form-group-half">
                                        <label for="provincia">Provincia</label>
                                        <select id="provincia" name="provincia" class="form-control">
                                            <option value="">Selecciona...</option>
                                            <option value="Distrito Nacional" <?php echo ($usuario['provincia'] ?? '') === 'Distrito Nacional' ? 'selected' : ''; ?>>Distrito Nacional</option>
                                            <option value="Santiago" <?php echo ($usuario['provincia'] ?? '') === 'Santiago' ? 'selected' : ''; ?>>Santiago</option>
                                            <option value="Santo Domingo" <?php echo ($usuario['provincia'] ?? '') === 'Santo Domingo' ? 'selected' : ''; ?>>Santo Domingo</option>
                                            <option value="La Romana" <?php echo ($usuario['provincia'] ?? '') === 'La Romana' ? 'selected' : ''; ?>>La Romana</option>
                                            <option value="San Pedro de Macorís" <?php echo ($usuario['provincia'] ?? '') === 'San Pedro de Macorís' ? 'selected' : ''; ?>>San Pedro de Macorís</option>
                                            <option value="Puerto Plata" <?php echo ($usuario['provincia'] ?? '') === 'Puerto Plata' ? 'selected' : ''; ?>>Puerto Plata</option>
                                            <option value="La Vega" <?php echo ($usuario['provincia'] ?? '') === 'La Vega' ? 'selected' : ''; ?>>La Vega</option>
                                            <option value="Otra" <?php echo ($usuario['provincia'] ?? '') === 'Otra' ? 'selected' : ''; ?>>Otra</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="horario_atencion">Horario de Atención</label>
                                    <input type="text" id="horario_atencion" name="horario_atencion" class="form-control"
                                           value="<?php echo htmlspecialchars($usuario['horario_atencion'] ?? ''); ?>"
                                           placeholder="Ej: Lun-Vie 9:00 AM - 6:00 PM">
                                </div>
                                
                                <div class="form-group">
                                    <label for="descripcion_empresa">Descripción</label>
                                    <textarea id="descripcion_empresa" name="descripcion_empresa" class="form-control" rows="4"
                                              placeholder="Describe tu agencia..."><?php echo htmlspecialchars($usuario['descripcion_empresa'] ?? ''); ?></textarea>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Seguridad -->
                <div class="profile-content-card" id="seguridad">
                    <div class="card-header">
                        <h2><i class="fas fa-lock"></i> Cambiar Contraseña</h2>
                    </div>
                    <div class="card-body">
                        <form action="/perfil.php" method="POST" class="profile-form">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="form-group">
                                <label for="current_password">Contraseña Actual</label>
                                <div class="password-input">
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword('current_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">Nueva Contraseña</label>
                                <div class="password-input">
                                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-text">Mínimo 12 caracteres, incluir mayúsculas, números y símbolos</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirmar Nueva Contraseña</label>
                                <div class="password-input">
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key"></i> Cambiar Contraseña
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
</section>

<?php
include __DIR__ . '/../includes/footer.php';
?>