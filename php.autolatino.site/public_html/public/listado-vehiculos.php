<?php
/**
 * MotoSpot - Listado de Vehículos
 * Búsqueda y listado de vehículos con filtros
 * 
 * @author Kevin
 * @version 1.0.0
 */

define('MOTO_SPOT', true);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';


// Parámetros de filtro
$filtros = [
    'q'          => trim($_GET['q'] ?? ''),
    'marca'      => $_GET['marca'] ?? '',
    'modelo'     => $_GET['modelo'] ?? '',
    'ano_desde'  => $_GET['ano_desde'] ?? '',
    'ano_hasta'  => $_GET['ano_hasta'] ?? '',
    'precio_min' => $_GET['precio_min'] ?? '',
    'precio_max' => $_GET['precio_max'] ?? '',
    'tipo'       => $_GET['tipo'] ?? '',
    'condicion'  => $_GET['condicion'] ?? '',
    'transmision'=> $_GET['transmision'] ?? '',
    'combustible'=> $_GET['combustible'] ?? '',
    'ciudad'     => $_GET['ciudad'] ?? '',
    'destacados' => isset($_GET['destacados']) ? true : false,
];

// Ordenamiento con whitelist (evita SQL injection)
$sortMap = [
    'precio_asc'  => 'v.precio ASC',
    'precio_desc' => 'v.precio DESC',
    'ano_desc'    => 'v.ano DESC',
    'ano_asc'     => 'v.ano ASC',
    'reciente'    => 'v.fecha_publicacion DESC',
];
$sort    = $_GET['sort'] ?? '';
$orderBy = $sortMap[$sort] ?? 'v.destacado DESC, v.fecha_publicacion DESC';

// Paginación
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 24;
$offset = ($page - 1) * $perPage;

// Construir consulta SQL (todas las tablas usan prefijo ms_)
$where = ["v.estado_publicacion = 'activo'"];
$params = [];

if (!empty($filtros['marca'])) {
    $where[] = "v.marca = ?";
    $params[] = $filtros['marca'];
}
if (!empty($filtros['modelo'])) {
    $where[] = "(v.modelo LIKE ? OR v.titulo LIKE ?)";
    $params[] = "%{$filtros['modelo']}%";
    $params[] = "%{$filtros['modelo']}%";
}
if (!empty($filtros['ano_desde'])) {
    $where[] = "v.ano >= ?";
    $params[] = $filtros['ano_desde'];
}
if (!empty($filtros['ano_hasta'])) {
    $where[] = "v.ano <= ?";
    $params[] = $filtros['ano_hasta'];
}
if (!empty($filtros['precio_min'])) {
    $where[] = "v.precio >= ?";
    $params[] = $filtros['precio_min'];
}
if (!empty($filtros['precio_max'])) {
    $where[] = "v.precio <= ?";
    $params[] = $filtros['precio_max'];
}
if (!empty($filtros['tipo'])) {
    $where[] = "v.tipo_vehiculo = ?";
    $params[] = $filtros['tipo'];
}
if (!empty($filtros['condicion'])) {
    $where[] = "v.condicion = ?";
    $params[] = $filtros['condicion'];
}
if (!empty($filtros['transmision'])) {
    $where[] = "v.transmision = ?";
    $params[] = $filtros['transmision'];
}
if (!empty($filtros['combustible'])) {
    $where[] = "v.combustible = ?";
    $params[] = $filtros['combustible'];
}
if (!empty($filtros['ciudad'])) {
    $where[] = "v.ciudad LIKE ?";
    $params[] = "%{$filtros['ciudad']}%";
}
if ($filtros['destacados']) {
    $where[] = "v.destacado = 1";
}

// Búsqueda por texto libre (q)
if (!empty($filtros['q'])) {
    $where[]  = "(v.titulo LIKE ? OR v.marca LIKE ? OR v.modelo LIKE ?)";
    $params[] = '%' . $filtros['q'] . '%';
    $params[] = '%' . $filtros['q'] . '%';
    $params[] = '%' . $filtros['q'] . '%';
}

$whereClause = implode(' AND ', $where);

// Contar total de resultados
$countSql = "SELECT COUNT(*) as total FROM ms_vehiculos v WHERE $whereClause";
$totalResultados = fetchOne($countSql, $params)['total'] ?? 0;
$totalPages = ceil($totalResultados / $perPage);

// Obtener vehículos — LIMIT/OFFSET parametrizados para evitar SQL injection
$sqlParams   = $params;
$sqlParams[] = $perPage;
$sqlParams[] = $offset;

$sql = "SELECT v.*, u.nombre as vendedor_nombre, u.nombre_agencia, u.tipo as vendedor_tipo, u.telefono as vendedor_telefono,
        f.url_foto as foto_principal
        FROM ms_vehiculos v
        JOIN ms_usuarios u ON v.usuario_id = u.id
        LEFT JOIN ms_vehiculo_fotos f ON f.vehiculo_id = v.id AND f.es_principal = 1
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT ? OFFSET ?";

$vehiculos = fetchAll($sql, $sqlParams);

// Opciones para filtros
$marcas = fetchAll("SELECT DISTINCT marca FROM ms_vehiculos WHERE estado_publicacion = 'activo' ORDER BY marca");
$ciudades = fetchAll("SELECT DISTINCT ciudad FROM ms_vehiculos WHERE estado_publicacion = 'activo' ORDER BY ciudad");

$tiposVehiculo = [
    'sedan' => 'Sedán',
    'suv' => 'SUV',
    'pickup' => 'Pickup',
    'hatchback' => 'Hatchback',
    'coupe' => 'Coupé',
    'convertible' => 'Convertible',
    'van' => 'Van',
    'minivan' => 'Minivan',
    'camioneta' => 'Camioneta',
    'deportivo' => 'Deportivo',
    'otro' => 'Otro'
];

$pageTitle = 'Buscar Vehículos';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<section class="listing-section">
    <div class="listing-container">
        <!-- Sidebar de Filtros -->
        <aside class="listing-filters" id="filtersSidebar">
            <div class="filters-header">
                <h2><i class="fas fa-filter"></i> Filtros</h2>
                <button class="btn btn-close-filters" onclick="toggleFilters()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="/listado-vehiculos.php" method="GET" class="filters-form" id="filtersForm">
                <!-- Marca -->
                <div class="filter-group">
                    <label class="filter-label">Marca</label>
                    <select name="marca" class="filter-select">
                        <option value="">Todas las marcas</option>
                        <?php foreach ($marcas as $m): ?>
                            <option value="<?php echo $m['marca']; ?>" 
                                    <?php echo $filtros['marca'] === $m['marca'] ? 'selected' : ''; ?>>
                                <?php echo $m['marca']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Modelo -->
                <div class="filter-group">
                    <label class="filter-label">Modelo</label>
                    <input type="text" name="modelo" class="filter-input" 
                           placeholder="Ej: Corolla"
                           value="<?php echo htmlspecialchars($filtros['modelo']); ?>">
                </div>
                
                <!-- Año -->
                <div class="filter-group">
                    <label class="filter-label">Año</label>
                    <div class="filter-range">
                        <select name="ano_desde" class="filter-select">
                            <option value="">Desde</option>
                            <?php for ($i = date('Y'); $i >= 1950; $i--): ?>
                                <option value="<?php echo $i; ?>" 
                                        <?php echo $filtros['ano_desde'] == $i ? 'selected' : ''; ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <select name="ano_hasta" class="filter-select">
                            <option value="">Hasta</option>
                            <?php for ($i = date('Y'); $i >= 1950; $i--): ?>
                                <option value="<?php echo $i; ?>" 
                                        <?php echo $filtros['ano_hasta'] == $i ? 'selected' : ''; ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Precio -->
                <div class="filter-group">
                    <label class="filter-label">Precio (USD)</label>
                    <div class="filter-range">
                        <input type="number" name="precio_min" class="filter-input" 
                               placeholder="Min" value="<?php echo $filtros['precio_min']; ?>">
                        <input type="number" name="precio_max" class="filter-input" 
                               placeholder="Max" value="<?php echo $filtros['precio_max']; ?>">
                    </div>
                </div>
                
                <!-- Tipo -->
                <div class="filter-group">
                    <label class="filter-label">Tipo de Vehículo</label>
                    <select name="tipo" class="filter-select">
                        <option value="">Todos</option>
                        <?php foreach ($tiposVehiculo as $key => $value): ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo $filtros['tipo'] === $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Condición -->
                <div class="filter-group">
                    <label class="filter-label">Condición</label>
                    <select name="condicion" class="filter-select">
                        <option value="">Todas</option>
                        <option value="nuevo" <?php echo $filtros['condicion'] === 'nuevo' ? 'selected' : ''; ?>>Nuevo (0km)</option>
                        <option value="seminuevo" <?php echo $filtros['condicion'] === 'seminuevo' ? 'selected' : ''; ?>>Seminuevo</option>
                        <option value="usado" <?php echo $filtros['condicion'] === 'usado' ? 'selected' : ''; ?>>Usado</option>
                    </select>
                </div>
                
                <!-- Transmisión -->
                <div class="filter-group">
                    <label class="filter-label">Transmisión</label>
                    <select name="transmision" class="filter-select">
                        <option value="">Todas</option>
                        <option value="automatica" <?php echo $filtros['transmision'] === 'automatica' ? 'selected' : ''; ?>>Automática</option>
                        <option value="manual" <?php echo $filtros['transmision'] === 'manual' ? 'selected' : ''; ?>>Manual</option>
                        <option value="cvt" <?php echo $filtros['transmision'] === 'cvt' ? 'selected' : ''; ?>>CVT</option>
                    </select>
                </div>
                
                <!-- Combustible -->
                <div class="filter-group">
                    <label class="filter-label">Combustible</label>
                    <select name="combustible" class="filter-select">
                        <option value="">Todos</option>
                        <option value="gasolina" <?php echo $filtros['combustible'] === 'gasolina' ? 'selected' : ''; ?>>Gasolina</option>
                        <option value="diesel" <?php echo $filtros['combustible'] === 'diesel' ? 'selected' : ''; ?>>Diésel</option>
                        <option value="hibrido" <?php echo $filtros['combustible'] === 'hibrido' ? 'selected' : ''; ?>>Híbrido</option>
                        <option value="electrico" <?php echo $filtros['combustible'] === 'electrico' ? 'selected' : ''; ?>>Eléctrico</option>
                    </select>
                </div>
                
                <!-- Ciudad -->
                <div class="filter-group">
                    <label class="filter-label">Ubicación</label>
                    <select name="ciudad" class="filter-select">
                        <option value="">Todas las ciudades</option>
                        <?php foreach ($ciudades as $c): ?>
                            <option value="<?php echo $c['ciudad']; ?>" 
                                    <?php echo $filtros['ciudad'] === $c['ciudad'] ? 'selected' : ''; ?>>
                                <?php echo $c['ciudad']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> Aplicar Filtros
                    </button>
                    <a href="/listado-vehiculos.php" class="btn btn-outline btn-block">
                        <i class="fas fa-undo"></i> Limpiar
                    </a>
                </div>
            </form>
        </aside>
        
        <!-- Overlay móvil -->
        <div class="filters-overlay" id="filtersOverlay" onclick="toggleFilters()"></div>
        
        <!-- Contenido Principal -->
        <main class="listing-content">
            <!-- Header del listado -->
            <div class="listing-header">
                <div class="listing-title">
                    <h1>
                        <?php if ($filtros['destacados']): ?>
                            <i class="fas fa-star"></i> Vehículos Destacados
                        <?php else: ?>
                            <i class="fas fa-search"></i> Buscar Vehículos
                        <?php endif; ?>
                    </h1>
                    <p class="listing-count">
                        <?php echo number_format($totalResultados); ?> resultados encontrados
                    </p>
                </div>
                <div class="listing-actions">
                    <button class="btn btn-filters-mobile" onclick="toggleFilters()">
                        <i class="fas fa-filter"></i> Filtros
                    </button>
                    <select class="sort-select" onchange="sortResults(this.value)">
                        <option value="recientes">Más recientes</option>
                        <option value="precio_asc">Precio: Menor a Mayor</option>
                        <option value="precio_desc">Precio: Mayor a Menor</option>
                        <option value="ano_desc">Año: Más nuevo</option>
                        <option value="ano_asc">Año: Más antiguo</option>
                    </select>
                </div>
            </div>
            
            <!-- Filtros activos -->
            <?php 
            $filtrosActivos = array_filter($filtros);
            if (!empty($filtrosActivos)): 
            ?>
                <div class="active-filters">
                    <span>Filtros activos:</span>
                    <?php if (!empty($filtros['marca'])): ?>
                        <span class="filter-tag">Marca: <?php echo $filtros['marca']; ?> <a href="?<?php echo http_build_query(array_diff_key($filtros, ['marca' => ''])); ?>">×</a></span>
                    <?php endif; ?>
                    <?php if (!empty($filtros['tipo'])): ?>
                        <span class="filter-tag">Tipo: <?php echo $tiposVehiculo[$filtros['tipo']] ?? $filtros['tipo']; ?> <a href="?<?php echo http_build_query(array_diff_key($filtros, ['tipo' => ''])); ?>">×</a></span>
                    <?php endif; ?>
                    <?php if (!empty($filtros['condicion'])): ?>
                        <span class="filter-tag">Condición: <?php echo ucfirst($filtros['condicion']); ?> <a href="?<?php echo http_build_query(array_diff_key($filtros, ['condicion' => ''])); ?>">×</a></span>
                    <?php endif; ?>
                    <a href="/listado-vehiculos.php" class="clear-all">Limpiar todos</a>
                </div>
            <?php endif; ?>
            
            <!-- Grid de vehículos -->
            <?php if (empty($vehiculos)): ?>
                <div class="empty-state">
                    <i class="fas fa-car-side"></i>
                    <h3>No se encontraron vehículos</h3>
                    <p>Intenta ajustar los filtros o busca con otros criterios</p>
                    <a href="/listado-vehiculos.php" class="btn btn-primary">Ver todos los vehículos</a>
                </div>
            <?php else: ?>
                <div class="vehicles-grid vehicles-grid-listing">
                    <?php foreach ($vehiculos as $vehiculo): ?>
                        <article class="vehicle-card">
                            <a href="/detalle-vehiculo.php?id=<?php echo $vehiculo['id']; ?>" class="vehicle-link">
                                <div class="vehicle-image">
                                    <?php if ($vehiculo['foto_principal']): ?>
                                        <img src="<?php echo htmlspecialchars($vehiculo['foto_principal']); ?>" 
                                             alt="<?php echo htmlspecialchars($vehiculo['titulo']); ?>" loading="lazy">
                                    <?php else: ?>
                                        <div class="vehicle-no-image">
                                            <i class="fas fa-car"></i>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($vehiculo['destacado']): ?>
                                        <span class="vehicle-badge vehicle-badge-featured vehicle-badge-tr">¡Destacado!</span>
                                    <?php endif; ?>
                                    <?php if ($vehiculo['condicion'] === 'nuevo'): ?>
                                        <span class="vehicle-badge vehicle-badge-new">0km</span>
                                    <?php endif; ?>
                                </div>
                                <div class="vehicle-info">
                                    <!-- Vista completa (detalle) -->
                                    <h3 class="vehicle-title"><?php echo htmlspecialchars($vehiculo['titulo']); ?></h3>
                                    <div class="vehicle-details">
                                        <span class="vehicle-year"><?php echo $vehiculo['ano']; ?></span>
                                        <span class="vehicle-km"><?php echo number_format($vehiculo['kilometraje']); ?> km</span>
                                        <span class="vehicle-trans"><?php echo ucfirst($vehiculo['transmision']); ?></span>
                                    </div>
                                    <div class="vehicle-price">
                                        <span class="price">$<?php echo number_format($vehiculo['precio'], 2); ?></span>
                                        <?php if ($vehiculo['precio_negociable']): ?>
                                            <span class="negotiable">Negociable</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="vehicle-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($vehiculo['ciudad']); ?>, <?php echo htmlspecialchars($vehiculo['provincia']); ?>
                                    </div>
                                    <div class="vehicle-seller">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($vehiculo['nombre_agencia'] ?: $vehiculo['vendedor_nombre']); ?>
                                    </div>
                                    <!-- Vista compacta (listado) -->
                                    <div class="vehicle-compact-info">
                                        <span class="vci-year-brand"><?php echo $vehiculo['ano'].' '.htmlspecialchars($vehiculo['marca']); ?></span>
                                        <span class="vci-model"><?php echo htmlspecialchars($vehiculo['modelo']); ?></span>
                                        <span class="vci-price">US$ <?php echo number_format($vehiculo['precio']); ?></span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
                
                <!-- Paginación -->
                <?php if ($totalPages > 1): ?>
                    <nav class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($filtros, ['page' => $page - 1])); ?>" class="page-link">
                                <i class="fas fa-chevron-left"></i> Anterior
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="page-link active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?<?php echo http_build_query(array_merge($filtros, ['page' => $i])); ?>" class="page-link">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?<?php echo http_build_query(array_merge($filtros, ['page' => $page + 1])); ?>" class="page-link">
                                Siguiente <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</section>

<?php
include __DIR__ . '/../includes/footer.php';
?>