<?php
/**
 * MotoSpot - Página de Registro
 * Registro de nuevos usuarios
 * 
 * @author Kevin
 * @version 1.0.0
 */

define('MOTO_SPOT', true);
require_once __DIR__ . '/../includes/auth.php';

// Redirigir si ya está autenticado
redirigirSiAutenticado();

$error = '';
$success = '';
$datos = [
    'tipo' => 'individual',
    'nombre' => '',
    'apellido' => '',
    'email' => '',
    'telefono' => '',
    'nombre_agencia' => '',
    'direccion' => '',
    'ciudad' => '',
    'provincia' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF token para seguridad
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verificarCSRFToken($csrf_token)) {
        $error = 'Token de seguridad inválido. Intente nuevamente.';
    } else {
        // Recoger datos del formulario
        $datos['tipo'] = $_POST['tipo'] ?? 'individual';
        $datos['nombre'] = trim($_POST['nombre'] ?? '');
        $datos['apellido'] = trim($_POST['apellido'] ?? '');
        $datos['email'] = trim($_POST['email'] ?? '');
        $datos['telefono'] = trim($_POST['telefono'] ?? '');
        $datos['nombre_agencia'] = trim($_POST['nombre_agencia'] ?? '');
        $datos['direccion'] = trim($_POST['direccion'] ?? '');
        $datos['ciudad'] = trim($_POST['ciudad'] ?? '');
        $datos['provincia'] = trim($_POST['provincia'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $terminos = isset($_POST['terminos']);
        
        // Validaciones
        if (empty($datos['nombre'])) {
            $error = 'El nombre es obligatorio';
        } elseif (empty($datos['email'])) {
            $error = 'El correo electrónico es obligatorio';
        } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido';
    } elseif (empty($datos['telefono'])) {
        $error = 'El teléfono es obligatorio';
    } elseif (!validarTelefono($datos['telefono'])) {
        $error = 'El teléfono no tiene un formato válido';
    } else {
        // Validar contraseña con políticas de seguridad mejoradas
        $passValidation = validarPasswordSegura($password, 8);
        if (!$passValidation['valid']) {
            $error = $passValidation['error'];
        }
    }
    
    if (empty($error) && $password !== $password_confirm) {
        $error = 'Las contraseñas no coinciden';
    } elseif (!$terminos) {
        $error = 'Debes aceptar los términos y condiciones';
    } elseif ($datos['tipo'] === 'agencia' && empty($datos['nombre_agencia'])) {
        $error = 'El nombre de la agencia es obligatorio';
    } else {
        // Intentar registrar
        $datos['password'] = $password;
        $resultado = registrarUsuario($datos);
        
        if ($resultado['success']) {
            $success = '¡Registro exitoso! Ahora puedes iniciar sesión.';
            // Limpiar formulario
            $datos = array_fill_keys(array_keys($datos), '');
            $datos['tipo'] = 'individual';
        } else {
            $error = $resultado['message'];
        }
    }
}

$pageTitle = 'Registro';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<section class="auth-section">
    <div class="auth-container">
        <div class="auth-box auth-box-large">
            <div class="auth-header">
                <h1><i class="fas fa-user-plus"></i> Crear Cuenta</h1>
                <p>Únete a MotoSpot y comienza a comprar o vender vehículos</p>
            </div>
            
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
                    <br><br>
                    <a href="/login.php" class="btn btn-primary">Iniciar Sesión</a>
                </div>
            <?php else: ?>
                
            <form action="/register.php" method="POST" class="auth-form" id="registerForm">
                
                <!-- CSRF Token for security -->
                <input type="hidden" name="csrf_token" value="<?= generarCSRFToken() ?>">
                
                <!-- Tipo de cuenta -->
                <div class="form-group">
                    <label>Tipo de Cuenta</label>
                    <div class="account-type-selector">
                        <label class="account-type-option <?php echo $datos['tipo'] === 'individual' ? 'active' : ''; ?>">
                            <input type="radio" name="tipo" value="individual" 
                                   <?php echo $datos['tipo'] === 'individual' ? 'checked' : ''; ?>>
                            <i class="fas fa-user"></i>
                            <span>Particular</span>
                            <small>Vendo mi vehículo personal</small>
                        </label>
                        <label class="account-type-option <?php echo $datos['tipo'] === 'agencia' ? 'active' : ''; ?>">
                            <input type="radio" name="tipo" value="agencia"
                                   <?php echo $datos['tipo'] === 'agencia' ? 'checked' : ''; ?>>
                            <i class="fas fa-building"></i>
                            <span>Agencia</span>
                            <small>Soy concesionario</small>
                        </label>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label for="nombre">
                            <i class="fas fa-user"></i> Nombre *
                        </label>
                        <input type="text" 
                               id="nombre" 
                               name="nombre" 
                               class="form-control" 
                               placeholder="Tu nombre"
                               value="<?php echo htmlspecialchars($datos['nombre']); ?>"
                               required>
                    </div>
                    
                    <div class="form-group form-group-half">
                        <label for="apellido">
                            <i class="fas fa-user"></i> Apellido
                        </label>
                        <input type="text" 
                               id="apellido" 
                               name="apellido" 
                               class="form-control" 
                               placeholder="Tu apellido"
                               value="<?php echo htmlspecialchars($datos['apellido']); ?>">
                    </div>
                </div>
                
                <!-- Campos específicos para agencias -->
                <div id="agencia-fields" class="agencia-fields" style="<?php echo $datos['tipo'] === 'agencia' ? 'display: block;' : 'display: none;' ?>">
                    <div class="form-group">
                        <label for="nombre_agencia">
                            <i class="fas fa-building"></i> Nombre de la Agencia *
                        </label>
                        <input type="text" 
                               id="nombre_agencia" 
                               name="nombre_agencia" 
                               class="form-control" 
                               placeholder="Nombre de tu agencia"
                               value="<?php echo htmlspecialchars($datos['nombre_agencia']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="direccion">
                            <i class="fas fa-map-marker-alt"></i> Dirección
                        </label>
                        <input type="text" 
                               id="direccion" 
                               name="direccion" 
                               class="form-control" 
                               placeholder="Dirección de la agencia"
                               value="<?php echo htmlspecialchars($datos['direccion']); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Correo Electrónico *
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control" 
                               placeholder="tucorreo@ejemplo.com"
                               value="<?php echo htmlspecialchars($datos['email']); ?>"
                               required>
                    </div>
                    
                    <div class="form-group form-group-half">
                        <label for="telefono">
                            <i class="fas fa-phone"></i> Teléfono *
                        </label>
                        <input type="tel" 
                               id="telefono" 
                               name="telefono" 
                               class="form-control" 
                               placeholder="809-000-0000"
                               value="<?php echo htmlspecialchars($datos['telefono']); ?>"
                               required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label for="ciudad">
                            <i class="fas fa-city"></i> Ciudad
                        </label>
                        <input type="text" 
                               id="ciudad" 
                               name="ciudad" 
                               class="form-control" 
                               placeholder="Tu ciudad"
                               value="<?php echo htmlspecialchars($datos['ciudad']); ?>">
                    </div>
                    
                    <div class="form-group form-group-half">
                        <label for="provincia">
                            <i class="fas fa-map"></i> Provincia
                        </label>
                        <select id="provincia" name="provincia" class="form-control">
                            <option value="">Selecciona...</option>
                            <option value="Distrito Nacional" <?php echo $datos['provincia'] === 'Distrito Nacional' ? 'selected' : ''; ?>>Distrito Nacional</option>
                            <option value="Santiago" <?php echo $datos['provincia'] === 'Santiago' ? 'selected' : ''; ?>>Santiago</option>
                            <option value="Santo Domingo" <?php echo $datos['provincia'] === 'Santo Domingo' ? 'selected' : ''; ?>>Santo Domingo</option>
                            <option value="La Romana" <?php echo $datos['provincia'] === 'La Romana' ? 'selected' : ''; ?>>La Romana</option>
                            <option value="San Pedro de Macorís" <?php echo $datos['provincia'] === 'San Pedro de Macorís' ? 'selected' : ''; ?>>San Pedro de Macorís</option>
                            <option value="Puerto Plata" <?php echo $datos['provincia'] === 'Puerto Plata' ? 'selected' : ''; ?>>Puerto Plata</option>
                            <option value="La Vega" <?php echo $datos['provincia'] === 'La Vega' ? 'selected' : ''; ?>>La Vega</option>
                            <option value="Otra" <?php echo $datos['provincia'] === 'Otra' ? 'selected' : ''; ?>>Otra</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label for="password">
                            <i class="fas fa-lock"></i> Contraseña *
                        </label>
                        <div class="password-input">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-control" 
                                   placeholder="Mínimo 6 caracteres"
                                   required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group form-group-half">
                        <label for="password_confirm">
                            <i class="fas fa-lock"></i> Confirmar Contraseña *
                        </label>
                        <div class="password-input">
                            <input type="password" 
                                   id="password_confirm" 
                                   name="password_confirm" 
                                   class="form-control" 
                                   placeholder="Repite tu contraseña"
                                   required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password_confirm')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terminos" id="terminos" required>
                        <span class="checkmark"></span>
                        Acepto los <a href="#" target="_blank">Términos y Condiciones</a> y la <a href="#" target="_blank">Política de Privacidad</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-large">
                    <i class="fas fa-user-plus"></i> Crear Cuenta
                </button>
            </form>
            
            <?php endif; ?>
            
            <div class="auth-footer">
                <p>¿Ya tienes una cuenta? <a href="/login.php">Inicia sesión</a></p>
            </div>
        </div>
    </div>
</section>

<?php
include __DIR__ . '/../includes/footer.php';
?>