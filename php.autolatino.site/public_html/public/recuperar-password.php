<?php
/**
 * MotoSpot — Recuperar contraseña (paso 1: solicitar enlace)
 */
defined('MOTO_SPOT') || define('MOTO_SPOT', true);

require_once __DIR__ . '/../includes/env.php';
loadEnv();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/logger.php';

if (session_status() === PHP_SESSION_NONE) session_start();

redirigirSiAutenticado();

$mensaje = '';
$tipo    = '';
$email   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'Por favor ingresa un email válido.';
        $tipo    = 'error';
    } else {
        try {
            $pdo = getDB();

            // Verificar si el email existe
            $stmt = $pdo->prepare("SELECT id, nombre FROM ms_usuarios WHERE email = ? AND estado = 'activo' LIMIT 1");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Siempre mostrar el mismo mensaje (evita enumerar emails)
            $mensaje = 'Si el email está registrado, recibirás un enlace para restablecer tu contraseña.';
            $tipo    = 'success';

            if ($usuario) {
                // Invalidar tokens anteriores
                $pdo->prepare("UPDATE ms_password_resets SET used = 1 WHERE email = ? AND used = 0")
                    ->execute([$email]);

                // Generar token seguro
                $token     = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hora

                $pdo->prepare("
                    INSERT INTO ms_password_resets (email, token, expires_at)
                    VALUES (?, ?, ?)
                ")->execute([$email, $token, $expiresAt]);

                // Enviar email
                $resetUrl = env('APP_URL') . '/reset-password.php?token=' . $token;
                $nombre   = htmlspecialchars($usuario['nombre']);
                $appUrl   = env('APP_URL', 'https://php.autolatino.site');

                $html = emailTemplate(
                    'Recuperar contraseña',
                    "<p>Hola <strong>{$nombre}</strong>,</p>
                     <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en MotoSpot.</p>
                     <p>Este enlace es válido por <strong>1 hora</strong>. Si no solicitaste este cambio, puedes ignorar este email.</p>",
                    'Restablecer mi contraseña',
                    $resetUrl
                );

                queueEmail($email, $usuario['nombre'], 'Recuperar contraseña — MotoSpot', $html);
                logInfo('[recuperar-password] Token generado para: ' . $email);
            }
        } catch (Throwable $e) {
            logError('[recuperar-password] Error: ' . $e->getMessage());
            $mensaje = 'Ocurrió un error. Intenta nuevamente en unos minutos.';
            $tipo    = 'error';
        }
    }
}

$pageTitle = 'Recuperar contraseña';
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
                <div class="auth-icon"><i class="fas fa-key"></i></div>
                <h1>Recuperar contraseña</h1>
                <p>Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña.</p>
            </div>

            <?php if ($mensaje): ?>
                <div class="alert alert-<?= $tipo ?>">
                    <i class="fas fa-<?= $tipo === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <?php if ($tipo !== 'success'): ?>
            <form method="POST" action="/recuperar-password.php">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope" style="margin-right:.4rem;color:#3ABBE5"></i>Correo electrónico</label>
                    <input type="email" id="email" name="email" class="form-control"
                           placeholder="tucorreo@ejemplo.com"
                           value="<?= htmlspecialchars($email) ?>" required autofocus>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane" style="margin-right:.5rem"></i>Enviar enlace de recuperación
                </button>
            </form>
            <?php endif; ?>

            <div class="auth-back">
                <a href="/login.php"><i class="fas fa-arrow-left" style="margin-right:.35rem"></i>Volver al inicio de sesión</a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
