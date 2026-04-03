<?php
/**
 * MotoSpot - Página de Error 404
 * Configurada en .htaccess: ErrorDocument 404 /404.php
 */

define('MOTO_SPOT', true);
require_once __DIR__ . '/../includes/env.php';
loadEnv();

http_response_code(404);

$pageTitle = 'Página no encontrada';
$url       = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '');

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<section style="min-height: 60vh; display: flex; align-items: center; justify-content: center;">
    <div style="text-align: center; padding: 2rem;">
        <div style="font-size: 6rem; margin-bottom: 1rem;">🔍</div>
        <h1 style="font-size: 5rem; font-weight: 800; color: var(--color-primary); margin: 0;">404</h1>
        <h2 style="margin: 0.5rem 0 1rem;">Página no encontrada</h2>
        <p style="color: var(--color-text-muted); max-width: 480px; margin: 0 auto 2rem;">
            La página <code style="background:rgba(255,255,255,0.1); padding:2px 6px; border-radius:4px;">
            <?php echo $url; ?></code> no existe o fue movida.
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="/index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Ir al inicio
            </a>
            <a href="/listado-vehiculos.php" class="btn btn-outline">
                <i class="fas fa-search"></i> Buscar vehículos
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
