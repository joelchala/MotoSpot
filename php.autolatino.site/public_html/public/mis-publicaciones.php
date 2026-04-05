<?php
/**
 * MotoSpot - Mis Publicaciones
 * Lista de vehículos publicados por el usuario autenticado
 */

define('MOTO_SPOT', true);
require_once __DIR__ . '/../includes/auth.php';

// Requerir autenticación
requerirAutenticacion('/mis-publicaciones.php');

$usuario    = getUsuarioActual();
$pageTitle  = 'Mis Publicaciones';
$accion     = '';

// Procesar acciones (pausar / activar / eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['vehiculo_id'])) {
    if (!verificarCSRFToken($_POST['csrf_token'] ?? '')) {
        header('Location: /mis-publicaciones.php');
        exit();
    }

    $vid    = intval($_POST['vehiculo_id']);
    $accion = $_POST['accion'];

    // Verificar que el vehículo pertenece al usuario
    $mine = fetchOne(
        "SELECT id FROM ms_vehiculos WHERE id = ? AND usuario_id = ?",
        [$vid, $usuario['id']]
    );

    if ($mine) {
        if ($accion === 'pausar') {
            executeQuery(
                "UPDATE ms_vehiculos SET estado_publicacion = 'pausado' WHERE id = ?",
                [$vid]
            );
        } elseif ($accion === 'activar') {
            executeQuery(
                "UPDATE ms_vehiculos SET estado_publicacion = 'activo' WHERE id = ?",
                [$vid]
            );
        }
    }

    header('Location: /mis-publicaciones.php');
    exit();
}

// Obtener publicaciones del usuario
$vehiculos = fetchAll(
    "SELECT v.*, f.url_foto as foto_principal
     FROM ms_vehiculos v
     LEFT JOIN ms_vehiculo_fotos f ON f.vehiculo_id = v.id AND f.es_principal = 1
     WHERE v.usuario_id = ?
     ORDER BY v.fecha_publicacion DESC",
    [$usuario['id']]
);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<section class="profile-section">
    <div class="container">
        <div class="publish-header">
            <h1><i class="fas fa-car"></i> Mis Publicaciones</h1>
            <a href="/publicar-vehiculo.php" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nueva Publicación
            </a>
        </div>

        <?php if (empty($vehiculos)): ?>
            <div class="empty-state">
                <i class="fas fa-car-side"></i>
                <h3>Aún no tenés publicaciones</h3>
                <p>¡Publicá tu primer vehículo y empezá a recibir consultas!</p>
                <a href="/publicar-vehiculo.php" class="btn btn-primary">Publicar Vehículo</a>
            </div>
        <?php else: ?>
            <div class="vehicles-grid">
                <?php foreach ($vehiculos as $v): ?>
                    <article class="vehicle-card">
                        <a href="/detalle-vehiculo.php?id=<?php echo $v['id']; ?>" class="vehicle-link">
                            <div class="vehicle-image">
                                <?php if ($v['foto_principal']): ?>
                                    <img src="<?php echo htmlspecialchars($v['foto_principal']); ?>"
                                         alt="<?php echo htmlspecialchars($v['titulo']); ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="vehicle-no-image"><i class="fas fa-car"></i></div>
                                <?php endif; ?>
                                <span class="vehicle-badge vehicle-badge-<?php echo $v['estado_publicacion'] === 'activo' ? 'new' : 'featured'; ?>">
                                    <?php echo ucfirst($v['estado_publicacion']); ?>
                                </span>
                            </div>
                            <div class="vehicle-info">
                                <h3 class="vehicle-title"><?php echo htmlspecialchars($v['titulo']); ?></h3>
                                <div class="vehicle-price">
                                    <span class="price">$<?php echo number_format($v['precio'], 2); ?></span>
                                </div>
                                <div class="vehicle-details">
                                    <span><i class="fas fa-eye"></i> <?php echo number_format($v['vistas']); ?> vistas</span>
                                    <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($v['fecha_publicacion'])); ?></span>
                                </div>
                            </div>
                        </a>
                        <div class="form-actions" style="padding: 0 1rem 1rem;">
                            <?php if ($v['estado_publicacion'] === 'activo'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generarCSRFToken(); ?>">
                                    <input type="hidden" name="vehiculo_id" value="<?php echo $v['id']; ?>">
                                    <input type="hidden" name="accion" value="pausar">
                                    <button type="submit" class="btn btn-outline btn-small">
                                        <i class="fas fa-pause"></i> Pausar
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generarCSRFToken(); ?>">
                                    <input type="hidden" name="vehiculo_id" value="<?php echo $v['id']; ?>">
                                    <input type="hidden" name="accion" value="activar">
                                    <button type="submit" class="btn btn-primary btn-small">
                                        <i class="fas fa-play"></i> Activar
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
