<?php
/**
 * MotoSpot — Restablecer contraseña (paso 2: nueva contraseña)
 */
defined('MOTO_SPOT') || define('MOTO_SPOT', true);

require_once __DIR__ . '/../includes/env.php';
loadEnv();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/logger.php';

if (session_status() === PHP_SESSION_NONE) session_start();

redirigirSiAutenticado();

$token   = trim($_GET['token'] ?? '');
$mensaje = '';
$tipo    = '';
$valido  = false;

// ── Validar token ─────────────────────────────────────────────────────────────
if (!$token) {
    $mensaje = 'Enlace inválido. Solicita un nuevo enlace de recuperación.';
    $tipo    = 'error';
} else {
    try {
        $pdo  = getDB();
        $stmt = $pdo->prepare("
            SELECT * FROM ms_password_resets
            WHERE token = ? AND used = 0 AND expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reset) {
            $mensaje = 'Este enlace ha expirado o ya fue utilizado. Solicita uno nuevo.';
            $tipo    = 'error';
        } else {
            $valido = true;
        }
    } catch (Throwable $e) {
        logError('[reset-password] Error validando token: ' . $e->getMessage());
        $mensaje = 'Ocurrió un error. Intenta nuevamente en unos minutos.';
        $tipo    = 'error';
    }
}

// ── Procesar nueva contraseña ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valido) {
    // Verificar CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verificarCSRFToken($csrf_token)) {
        $mensaje = 'Token de seguridad inválido. Intente nuevamente.';
        $tipo    = 'error';
    } else {
        $nueva    = $_POST['password']         ?? '';
        $confirma = $_POST['password_confirm'] ?? '';

        // Validar contraseña con políticas mejoradas
        $passValidation = validarPasswordSegura($nueva, 8);
        if (!$passValidation['valid']) {
            $mensaje = $passValidation['error'];
            $tipo    = 'error';
        } elseif ($nueva !== $confirma) {
            $mensaje = 'Las contraseñas no coinciden.';
            $tipo    = 'error';
        } else {
            try {
                $hash = password_hash($nueva, PASSWORD_BCRYPT);

                // Actualizar contraseña
                $pdo->prepare("UPDATE ms_usuarios SET password = ? WHERE email = ?")
                    ->execute([$hash, $reset['email']]);

                // Invalidar token
                $pdo->prepare("UPDATE ms_password_resets SET used = 1 WHERE token = ?")
                    ->execute([$token]);

                logInfo('[reset-password] Contraseña restablecida para: ' . $reset['email']);

                $mensaje = 'Contraseña restablecida con éxito. Ya puedes iniciar sesión.';
                $tipo    = 'success';
                $valido  = false; // ocultar formulario
            } catch (Throwable $e) {
                logError('[reset-password] Error actualizando contraseña: ' . $e->getMessage());
                $mensaje = 'Ocurrió un error. Intenta nuevamente en unos minutos.';
                $tipo    = 'error';
            }
        }
    }
}

$pageTitle = 'Restablecer contraseña';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<style>
.auth-section{min-height:calc(100vh - 80px);display:flex;align-items:center;justify-content:center;padding:4rem 1rem;background:#0d0d1a}
.auth-container{width:100%;max-width:460px}
.auth-box{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:2.5rem}
.auth-header{text-align:center;margin-bottom:2rem}
.auth-header h1{font-size:1.75rem;font-weight:800;color:#fff;margin-bottom:.5rem}
.auth-header p{color:rgba(255,255,255,.55);font-size:.9rem;line-height:1.6}
.auth-icon{width:64px;height:64px;background:rgba(58,187,229,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-size:1.5rem;color:#3ABBE5}
.form-group{margin-bottom:1.25rem}
.form-group label{display:block;font-size:.875rem;font-weight:600;color:rgba(255,255,255,.8);margin-bottom:.5rem}
.form-control{width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:10px;color:#fff;padding:.75rem 1rem;font-size:.95rem;outline:none;transition:border-color .2s;box-sizing:border-box}
.form-control:focus{border-color:#3ABBE5;background:rgba(58,187,229,.06)}
.form-control::placeholder{color:rgba(255,255,255,.3)}
.password-wrap{position:relative}
.password-wrap .toggle-pw{position:absolute;right:.85rem;top:50%;transform:translateY(-50%);background:none;border:none;color:rgba(255,255,255,.4);cursor:pointer;font-size:.95rem;padding:0}
.password-wrap .toggle-pw:hover{color:#3ABBE5}
.strength-bar{height:4px;border-radius:2px;margin-top:.5rem;background:rgba(255,255,255,.08);overflow:hidden}
.strength-fill{height:100%;width:0;border-radius:2px;transition:width .3s,background .3s}
.strength-text{font-size:.75rem;color:rgba(255,255,255,.4);margin-top:.3rem}
.btn-submit{width:100%;background:linear-gradient(135deg,#3ABBE5,#2AA5CC);color:#fff;border:none;border-radius:10px;padding:.9rem;font-size:1rem;font-weight:700;cursor:pointer;transition:all .2s;margin-top:.5rem}
.btn-submit:hover{box-shadow:0 8px 24px rgba(58,187,229,.4);transform:translateY(-1px)}
.alert{padding:.85rem 1rem;border-radius:10px;font-size:.875rem;margin-bottom:1.25rem;display:flex;align-items:flex-start;gap:.6rem}
.alert-success{background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.3);color:#4ade80}
.alert-error  {background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#f87171}
.auth-back{text-align:center;margin-top:1.5rem;font-size:.875rem;color:rgba(255,255,255,.5)}
.auth-back a{color:#3ABBE5;text-decoration:none;font-weight:600}
.auth-back a:hover{text-decoration:underline}
</style>

<section class="auth-section">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <div class="auth-icon"><i class="fas fa-lock-open"></i></div>
                <h1>Nueva contraseña</h1>
                <p>Elige una contraseña segura para tu cuenta.</p>
            </div>

            <?php if ($mensaje): ?>
                <div class="alert alert-<?= $tipo ?>">
                    <i class="fas fa-<?= $tipo === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <?php if ($valido): ?>
            <form method="POST" action="/reset-password.php?token=<?= urlencode($token) ?>">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= generarCSRFToken() ?>">
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock" style="margin-right:.4rem;color:#3ABBE5"></i>Nueva contraseña</label>
                    <div class="password-wrap">
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="Mínimo 8 caracteres" required autofocus
                               oninput="checkStrength(this.value)">
                        <button type="button" class="toggle-pw" onclick="togglePw('password',this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <div class="strength-text" id="strengthText"></div>
                </div>

                <div class="form-group">
                    <label for="password_confirm"><i class="fas fa-lock" style="margin-right:.4rem;color:#3ABBE5"></i>Confirmar contraseña</label>
                    <div class="password-wrap">
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                               placeholder="Repite tu contraseña" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('password_confirm',this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-check" style="margin-right:.5rem"></i>Guardar nueva contraseña
                </button>
            </form>
            <?php elseif ($tipo === 'success'): ?>
                <div style="text-align:center;margin-top:1rem">
                    <a href="/login.php" class="btn-submit" style="display:inline-block;text-decoration:none;padding:.9rem 2rem">
                        <i class="fas fa-sign-in-alt" style="margin-right:.5rem"></i>Ir al inicio de sesión
                    </a>
                </div>
            <?php endif; ?>

            <div class="auth-back">
                <a href="/recuperar-password.php"><i class="fas fa-redo" style="margin-right:.35rem"></i>Solicitar nuevo enlace</a>
                &nbsp;·&nbsp;
                <a href="/login.php"><i class="fas fa-arrow-left" style="margin-right:.35rem"></i>Iniciar sesión</a>
            </div>
        </div>
    </div>
</section>

<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function checkStrength(val) {
    const fill = document.getElementById('strengthFill');
    const text = document.getElementById('strengthText');
    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        {pct:'20%', color:'#ef4444', label:'Muy débil'},
        {pct:'40%', color:'#f97316', label:'Débil'},
        {pct:'60%', color:'#eab308', label:'Regular'},
        {pct:'80%', color:'#22c55e', label:'Fuerte'},
        {pct:'100%',color:'#10b981', label:'Muy fuerte'},
    ];
    const lvl = levels[Math.max(0, score - 1)] || levels[0];
    fill.style.width     = val.length ? lvl.pct   : '0';
    fill.style.background= val.length ? lvl.color : 'transparent';
    text.textContent     = val.length ? lvl.label  : '';
    text.style.color     = val.length ? lvl.color  : '';
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
