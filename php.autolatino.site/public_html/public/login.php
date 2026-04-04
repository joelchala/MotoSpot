<?php
/**
 * MotoSpot - Página de Login
 * Inicio de sesión de usuarios
 * 
 * @author Kevin
 * @version 1.0.0
 */

define('MOTO_SPOT', true);
require_once __DIR__ . '/../includes/auth.php';

// Redirigir si ya está autenticado
redirigirSiAutenticado();

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF token para seguridad
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verificarCSRFToken($csrf_token)) {
        $error = 'Token de seguridad inválido. Intente nuevamente.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        if (empty($email) || empty($password)) {
            $error = 'Por favor, ingrese su correo electrónico y contraseña';
        } else {
            $resultado = login($email, $password);
            
            if ($resultado['success']) {
                // Redirigir a la página solicitada o al home (validar que sea ruta interna)
                $redirect = validarURL($_GET['redirect'] ?? '', [
                    '/index.php',
                    '/listado-vehiculos.php',
                    '/mis-publicaciones.php',
                    '/perfil.php',
                    '/planes.php',
                    '/embarcaciones.php'
                ]);
                header("Location: $redirect");
                exit();
        } else {
            $error = $resultado['message'];
        }
    }
}

$pageTitle = 'Iniciar Sesión';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<section class="auth-section">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</h1>
                <p>Ingresa a tu cuenta para gestionar tus publicaciones</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form action="/login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" 
                  method="POST" 
                  class="auth-form"
                  id="loginForm">
                
                <!-- CSRF Token for security -->
                <input type="hidden" name="csrf_token" value="<?= generarCSRFToken() ?>">
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Correo Electrónico
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           placeholder="tucorreo@ejemplo.com"
                           value="<?php echo htmlspecialchars($email); ?>"
                           required
                           autocomplete="email">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                    <div class="password-input">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="Tu contraseña"
                               required
                               autocomplete="current-password">
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group form-group-inline">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="checkmark"></span>
                        Recordarme
                    </label>
                    <a href="/recuperar-password.php" class="forgot-password">¿Olvidaste tu contraseña?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-large">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </form>
            
            <div class="auth-divider">
                <span>O</span>
            </div>
            
            <div class="auth-social">
                <p>Inicia sesión con:</p>
                <div id="googleBtnWrap" style="display:flex;justify-content:center;margin-bottom:.5rem"></div>
                <div id="googleBtnFallback" style="display:none">
                    <a href="/oauth-google.php" class="btn btn-social btn-google">
                        <i class="fab fa-google"></i> Google
                    </a>
                </div>
                <div style="margin-top:.5rem">
                    <button type="button" class="btn btn-social btn-facebook" disabled title="Próximamente">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </button>
                </div>
            </div>

            <div class="auth-footer">
                <p>¿No tienes una cuenta? <a href="/register.php">Regístrate gratis</a></p>
            </div>
        </div>
    </div>
</section>

<!-- Google Identity Services -->
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
const GOOGLE_CLIENT_ID = '<?= htmlspecialchars(getenv('GOOGLE_CLIENT_ID') ?: '') ?>';

if (!GOOGLE_CLIENT_ID) {
    console.warn('Google Client ID not configured. Set GOOGLE_CLIENT_ID in .env');
}

window.addEventListener('load', function () {
    if (typeof google === 'undefined' || !google.accounts) {
        // GIS no cargó — mostrar fallback
        document.getElementById('googleBtnFallback').style.display = 'block';
        return;
    }

    if (!GOOGLE_CLIENT_ID) {
        console.error('Cannot initialize Google Sign-In: missing GOOGLE_CLIENT_ID');
        document.getElementById('googleBtnFallback').style.display = 'block';
        return;
    }

    google.accounts.id.initialize({
        client_id: GOOGLE_CLIENT_ID,
        callback: handleGoogleCredential,
        auto_select: false,
        cancel_on_tap_outside: true,
    });

    google.accounts.id.renderButton(
        document.getElementById('googleBtnWrap'),
        {
            theme: 'outline',
            size: 'large',
            text: 'signin_with',
            shape: 'rectangular',
            logo_alignment: 'left',
            width: 340,
        }
    );
});

async function handleGoogleCredential(response) {
    try {
        const res = await fetch('/auth-google-token.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ credential: response.credential }),
        });
        const data = await res.json();

        if (data.success) {
            window.location.href = data.redirect || '/';
        } else {
            showGoogleError(data.error || 'Error al iniciar sesión con Google');
        }
    } catch (e) {
        showGoogleError('Error de conexión. Intenta nuevamente.');
    }
}

function showGoogleError(msg) {
    const el = document.getElementById('googleError');
    if (el) { el.textContent = msg; el.style.display = 'flex'; return; }
    const div = document.createElement('div');
    div.id = 'googleError';
    div.className = 'alert alert-error';
    div.style.cssText = 'margin-top:.75rem';
    div.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + msg;
    document.getElementById('googleBtnWrap').after(div);
}
</script>

<?php
include __DIR__ . '/../includes/footer.php';
?>