<?php
/**
 * MotoSpot - Embarcaciones
 * Listado de embarcaciones (lanchas, jet skis, yates)
 */

define('MOTO_SPOT', true);
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Embarcaciones';

// Filtros
$tipo      = $_GET['tipo']      ?? '';
$precioMin = $_GET['precio_min'] ?? '';
$precioMax = $_GET['precio_max'] ?? '';
$page      = max(1, intval($_GET['page'] ?? 1));
$perPage   = 12;
$offset    = ($page - 1) * $perPage;

// Construir WHERE
$where  = ["v.estado_publicacion = 'activo'", "v.tipo_vehiculo IN ('lancha','jet-ski','yate','embarcacion')"];
$params = [];

if (!empty($tipo)) {
    $where[]  = "v.tipo_vehiculo = ?";
    $params[] = $tipo;
}
if (!empty($precioMin)) {
    $where[]  = "v.precio >= ?";
    $params[] = $precioMin;
}
if (!empty($precioMax)) {
    $where[]  = "v.precio <= ?";
    $params[] = $precioMax;
}

$whereClause = implode(' AND ', $where);

$total       = fetchOne("SELECT COUNT(*) as total FROM ms_vehiculos v WHERE $whereClause", $params)['total'] ?? 0;
$totalPages  = ceil($total / $perPage);

// LIMIT/OFFSET parametrizados para evitar SQL injection
$sqlParams   = $params;
$sqlParams[] = $perPage;
$sqlParams[] = $offset;

$sql = "SELECT v.*, u.nombre as vendedor_nombre, u.nombre_agencia,
        f.url_foto as foto_principal
        FROM ms_vehiculos v
        JOIN ms_usuarios u ON v.usuario_id = u.id
        LEFT JOIN ms_vehiculo_fotos f ON f.vehiculo_id = v.id AND f.es_principal = 1
        WHERE $whereClause
        ORDER BY v.destacado DESC, v.fecha_publicacion DESC
        LIMIT ? OFFSET ?";

$embarcaciones = fetchAll($sql, $sqlParams);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<section class="listing-section">
    <div class="listing-container">
        <main class="listing-content">
            <div class="listing-header">
                <div class="listing-title">
                    <h1><i class="fas fa-ship"></i> Embarcaciones</h1>
                    <p class="listing-count"><?php echo number_format($total); ?> embarcaciones encontradas</p>
                </div>
            </div>

            <!-- Filtros rápidos -->
            <div class="active-filters" style="margin-bottom: 1.5rem;">
                <a href="/embarcaciones.php" class="filter-tag <?php echo empty($tipo) ? 'active' : ''; ?>">Todas</a>
                <a href="/embarcaciones.php?tipo=lancha" class="filter-tag <?php echo $tipo === 'lancha' ? 'active' : ''; ?>">Lanchas</a>
                <a href="/embarcaciones.php?tipo=jet-ski" class="filter-tag <?php echo $tipo === 'jet-ski' ? 'active' : ''; ?>">Jet Ski</a>
                <a href="/embarcaciones.php?tipo=yate" class="filter-tag <?php echo $tipo === 'yate' ? 'active' : ''; ?>">Yates</a>
            </div>

            <?php if (empty($embarcaciones)): ?>
                <div class="empty-state">
                    <i class="fas fa-ship"></i>
                    <h3>No hay embarcaciones disponibles</h3>
                    <p>Sé el primero en publicar una embarcación en MotoSpot</p>
                    <a href="/publicar-vehiculo.php" class="btn btn-primary">Publicar Embarcación</a>
                </div>
            <?php else: ?>
                <div class="vehicles-grid vehicles-grid-listing">
                    <?php foreach ($embarcaciones as $item): ?>
                        <article class="vehicle-card">
                            <a href="/detalle-vehiculo.php?id=<?php echo $item['id']; ?>" class="vehicle-link">
                                <div class="vehicle-image">
                                    <?php if ($item['foto_principal']): ?>
                                        <img src="<?php echo htmlspecialchars($item['foto_principal']); ?>"
                                             alt="<?php echo htmlspecialchars($item['titulo']); ?>" loading="lazy">
                                    <?php else: ?>
                                        <div class="vehicle-no-image"><i class="fas fa-ship"></i></div>
                                    <?php endif; ?>
                                </div>
                                <div class="vehicle-info">
                                    <h3 class="vehicle-title"><?php echo htmlspecialchars($item['titulo']); ?></h3>
                                    <div class="vehicle-price">
                                        <span class="price">$<?php echo number_format($item['precio'], 2); ?></span>
                                        <?php if ($item['precio_negociable']): ?>
                                            <span class="negotiable">Negociable</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="vehicle-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($item['ciudad']); ?>, <?php echo htmlspecialchars($item['provincia']); ?>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?tipo=<?php echo urlencode($tipo); ?>&page=<?php echo $page - 1; ?>" class="page-link">
                                <i class="fas fa-chevron-left"></i> Anterior
                            </a>
                        <?php endif; ?>
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="page-link active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?tipo=<?php echo urlencode($tipo); ?>&page=<?php echo $i; ?>" class="page-link"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?tipo=<?php echo urlencode($tipo); ?>&page=<?php echo $page + 1; ?>" class="page-link">
                                Siguiente <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
