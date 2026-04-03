<?php
/**
 * MotoSpot - Detalle de Vehículo
 * Página de detalle individual de vehículo
 * 
 * @author Kevin
 * @version 1.0.0
 */

define('MOTO_SPOT', true);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Obtener ID del vehículo
$vehiculoId = intval($_GET['id'] ?? 0);

if (!$vehiculoId) {
    header('Location: /listado-vehiculos.php');
    exit();
}

// Obtener información del vehículo
$sql = "SELECT v.*, u.nombre as vendedor_nombre, u.apellido as vendedor_apellido,
        u.nombre_agencia, u.tipo as vendedor_tipo, u.telefono as vendedor_telefono,
        u.telefono_adicional, u.email as vendedor_email, u.direccion, u.horario_atencion,
        u.descripcion_empresa, u.fecha_registro as vendedor_registro
        FROM ms_vehiculos v
        JOIN ms_usuarios u ON v.usuario_id = u.id
        WHERE v.id = ? AND v.estado_publicacion = 'activo'";

$vehiculo = fetchOne($sql, [$vehiculoId]);

if (!$vehiculo) {
    header('Location: /listado-vehiculos.php');
    exit();
}

// Obtener fotos del vehículo
$fotos = fetchAll(
    "SELECT * FROM ms_vehiculo_fotos WHERE vehiculo_id = ? ORDER BY es_principal DESC, orden ASC",
    [$vehiculoId]
);

// Incrementar contador de vistas
executeQuery("UPDATE ms_vehiculos SET vistas = vistas + 1 WHERE id = ?", [$vehiculoId]);

// Verificar si está en favoritos
$esFavorito = false;
if (estaAutenticado()) {
    $fav = fetchOne(
        "SELECT id FROM ms_favoritos WHERE usuario_id = ? AND vehiculo_id = ?",
        [getUsuarioId(), $vehiculoId]
    );
    $esFavorito = !empty($fav);
}

// Vehículos similares
$vehiculosSimilares = fetchAll(
    "SELECT v.*, u.nombre as vendedor_nombre, u.nombre_agencia,
     (SELECT url_foto FROM ms_vehiculo_fotos WHERE vehiculo_id = v.id AND es_principal = 1 LIMIT 1) as foto_principal
     FROM ms_vehiculos v
     JOIN ms_usuarios u ON v.usuario_id = u.id
     WHERE v.id != ? AND v.estado_publicacion = 'activo' AND v.marca = ?
     ORDER BY v.fecha_publicacion DESC
     LIMIT 4",
    [$vehiculoId, $vehiculo['marca']]
);

$pageTitle = $vehiculo['titulo'];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<section class="vehicle-detail-section">
    <div class="container">
        <!-- Breadcrumbs -->
        <nav class="breadcrumbs">
            <a href="/index.php">Inicio</a>
            <span>/</span>
            <a href="/listado-vehiculos.php">Vehículos</a>
            <span>/</span>
            <span><?php echo htmlspecialchars($vehiculo['marca']); ?></span>
            <span>/</span>
            <span class="current"><?php echo htmlspecialchars($vehiculo['modelo']); ?></span>
        </nav>
        
        <div class="vehicle-detail-grid">
            <!-- Columna Principal -->
            <div class="vehicle-detail-main">
                <!-- Galería de Fotos -->
                <div class="vehicle-gallery">
                    <?php if (!empty($fotos)): ?>
                        <div class="gallery-main">
                            <img src="<?php echo htmlspecialchars($fotos[0]['url_foto']); ?>" 
                                 alt="<?php echo htmlspecialchars($vehiculo['titulo']); ?>" 
                                 id="mainImage">
                            <?php if ($vehiculo['condicion'] === 'nuevo'): ?>
                                <span class="gallery-badge gallery-badge-new">0km</span>
                            <?php endif; ?>
                        </div>
                        <?php if (count($fotos) > 1): ?>
                            <div class="gallery-thumbnails">
                                <?php foreach ($fotos as $index => $foto): ?>
                                    <img src="<?php echo htmlspecialchars($foto['url_foto']); ?>" 
                                         alt="Foto <?php echo $index + 1; ?>"
                                         class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                                         onclick="changeMainImage(this)">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="gallery-main gallery-no-image">
                            <i class="fas fa-car"></i>
                            <p>Sin fotos disponibles</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Información Principal -->
                <div class="vehicle-info-card">
                    <div class="vehicle-header">
                        <h1><?php echo htmlspecialchars($vehiculo['titulo']); ?></h1>
                        <div class="vehicle-actions">
                            <button class="btn-action <?php echo $esFavorito ? 'active' : ''; ?>" 
                                    onclick="toggleFavorito(<?php echo $vehiculoId; ?>)"
                                    title="Agregar a favoritos">
                                <i class="<?php echo $esFavorito ? 'fas' : 'far'; ?> fa-heart"></i>
                            </button>
                            <button class="btn-action" onclick="compartir()" title="Compartir">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="vehicle-price-main">
                        <span class="price">$<?php echo number_format($vehiculo['precio'], 2); ?></span>
                        <?php if ($vehiculo['precio_negociable']): ?>
                            <span class="negotiable-badge">Negociable</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="vehicle-meta">
                        <span><i class="fas fa-calendar"></i> <?php echo $vehiculo['ano']; ?></span>
                        <span><i class="fas fa-tachometer-alt"></i> <?php echo number_format($vehiculo['kilometraje']); ?> km</span>
                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($vehiculo['ciudad']); ?></span>
                        <span><i class="fas fa-clock"></i> Publicado <?php echo date('d/m/Y', strtotime($vehiculo['fecha_publicacion'])); ?></span>
                    </div>
                </div>
                
                <!-- Especificaciones -->
                <div class="vehicle-info-card">
                    <h2><i class="fas fa-info-circle"></i> Especificaciones</h2>
                    <div class="specs-grid">
                        <div class="spec-item">
                            <span class="spec-label">Marca</span>
                            <span class="spec-value"><?php echo htmlspecialchars($vehiculo['marca']); ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Modelo</span>
                            <span class="spec-value"><?php echo htmlspecialchars($vehiculo['modelo']); ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Año</span>
                            <span class="spec-value"><?php echo $vehiculo['ano']; ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Condición</span>
                            <span class="spec-value"><?php echo ucfirst($vehiculo['condicion']); ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Tipo</span>
                            <span class="spec-value"><?php echo ucfirst($vehiculo['tipo_vehiculo']); ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Transmisión</span>
                            <span class="spec-value"><?php echo ucfirst($vehiculo['transmision']); ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Combustible</span>
                            <span class="spec-value"><?php echo ucfirst($vehiculo['combustible']); ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Tracción</span>
                            <span class="spec-value"><?php echo ucfirst($vehiculo['traccion']); ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Kilometraje</span>
                            <span class="spec-value"><?php echo number_format($vehiculo['kilometraje']); ?> km</span>
                        </div>
                        <?php if ($vehiculo['motor']): ?>
                            <div class="spec-item">
                                <span class="spec-label">Motor</span>
                                <span class="spec-value"><?php echo htmlspecialchars($vehiculo['motor']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($vehiculo['cilindraje']): ?>
                            <div class="spec-item">
                                <span class="spec-label">Cilindraje</span>
                                <span class="spec-value"><?php echo $vehiculo['cilindraje']; ?> L</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($vehiculo['color_exterior']): ?>
                            <div class="spec-item">
                                <span class="spec-label">Color Exterior</span>
                                <span class="spec-value"><?php echo htmlspecialchars($vehiculo['color_exterior']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($vehiculo['color_interior']): ?>
                            <div class="spec-item">
                                <span class="spec-label">Color Interior</span>
                                <span class="spec-value"><?php echo htmlspecialchars($vehiculo['color_interior']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($vehiculo['puertas']): ?>
                            <div class="spec-item">
                                <span class="spec-label">Puertas</span>
                                <span class="spec-value"><?php echo $vehiculo['puertas']; ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($vehiculo['pasajeros']): ?>
                            <div class="spec-item">
                                <span class="spec-label">Pasajeros</span>
                                <span class="spec-value"><?php echo $vehiculo['pasajeros']; ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Descripción -->
                <?php if ($vehiculo['descripcion']): ?>
                    <div class="vehicle-info-card">
                        <h2><i class="fas fa-align-left"></i> Descripción</h2>
                        <div class="vehicle-description">
                            <?php echo nl2br(htmlspecialchars($vehiculo['descripcion'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Vehículos Similares -->
                <?php if (!empty($vehiculosSimilares)): ?>
                    <div class="vehicle-info-card">
                        <h2><i class="fas fa-car"></i> Vehículos Similares</h2>
                        <div class="similar-vehicles">
                            <?php foreach ($vehiculosSimilares as $similar): ?>
                                <a href="/detalle-vehiculo.php?id=<?php echo $similar['id']; ?>" class="similar-vehicle">
                                    <?php if ($similar['foto_principal']): ?>
                                        <img src="<?php echo htmlspecialchars($similar['foto_principal']); ?>" 
                                             alt="<?php echo htmlspecialchars($similar['titulo']); ?>">
                                    <?php else: ?>
                                        <div class="similar-no-image"><i class="fas fa-car"></i></div>
                                    <?php endif; ?>
                                    <div class="similar-info">
                                        <h4><?php echo htmlspecialchars($similar['titulo']); ?></h4>
                                        <span class="similar-price">$<?php echo number_format($similar['precio'], 2); ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <aside class="vehicle-detail-sidebar">
                <!-- Contacto Vendedor -->
                <div class="sidebar-card">
                    <h3><i class="fas fa-user"></i> Contactar Vendedor</h3>
                    <div class="seller-info">
                        <?php if ($vehiculo['vendedor_tipo'] === 'agencia'): ?>
                            <div class="seller-type">
                                <i class="fas fa-building"></i> Agencia
                            </div>
                            <h4 class="seller-name"><?php echo htmlspecialchars($vehiculo['nombre_agencia']); ?></h4>
                            <?php if ($vehiculo['descripcion_empresa']): ?>
                                <p class="seller-desc"><?php echo htmlspecialchars(substr($vehiculo['descripcion_empresa'], 0, 150)); ?>...</p>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="seller-type">
                                <i class="fas fa-user"></i> Vendedor Particular
                            </div>
                            <h4 class="seller-name"><?php echo htmlspecialchars($vehiculo['vendedor_nombre'] . ' ' . $vehiculo['vendedor_apellido']); ?></h4>
                        <?php endif; ?>
                        
                        <div class="seller-since">
                            <i class="fas fa-calendar-check"></i> 
                            Miembro desde <?php echo date('M Y', strtotime($vehiculo['vendedor_registro'])); ?>
                        </div>
                    </div>
                    
                    <div class="contact-buttons">
                        <?php if ($vehiculo['vendedor_telefono']): ?>
                            <a href="tel:<?php echo $vehiculo['vendedor_telefono']; ?>" class="btn btn-contact btn-phone">
                                <i class="fas fa-phone"></i> Llamar
                            </a>
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $vehiculo['vendedor_telefono']); ?>" 
                               target="_blank" class="btn btn-contact btn-whatsapp">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Formulario de Contacto -->
                <div class="sidebar-card">
                    <h3><i class="fas fa-envelope"></i> Enviar Mensaje</h3>
                    <form action="/contactar.php" method="POST" class="contact-form" id="contactForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?= generarCSRFToken() ?>">
                        <input type="hidden" name="vehiculo_id" value="<?php echo $vehiculoId; ?>">
                        
                        <?php if (!estaAutenticado()): ?>
                            <div class="form-group">
                                <input type="text" name="nombre" class="form-control" placeholder="Tu nombre" required>
                            </div>
                            <div class="form-group">
                                <input type="email" name="email" class="form-control" placeholder="Tu correo" required>
                            </div>
                            <div class="form-group">
                                <input type="tel" name="telefono" class="form-control" placeholder="Tu teléfono">
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <textarea name="mensaje" class="form-control" rows="4" placeholder="Escribe tu mensaje..." required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane"></i> Enviar Mensaje
                        </button>
                    </form>
                </div>
                
                <!-- Ubicación -->
                <div class="sidebar-card">
                    <h3><i class="fas fa-map-marker-alt"></i> Ubicación</h3>
                    <div class="location-info">
                        <p><strong><?php echo htmlspecialchars($vehiculo['ciudad']); ?></strong></p>
                        <p><?php echo htmlspecialchars($vehiculo['provincia']); ?></p>
                        <?php if ($vehiculo['direccion']): ?>
                            <p class="location-address"><?php echo htmlspecialchars($vehiculo['direccion']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Consejos de Seguridad -->
                <div class="sidebar-card sidebar-card-tips">
                    <h3><i class="fas fa-shield-alt"></i> Consejos de Seguridad</h3>
                    <ul class="safety-tips">
                        <li>Verifica la documentación del vehículo</li>
                        <li>Inspecciona el vehículo personalmente</li>
                        <li>Realiza la transacción en un lugar público</li>
                        <li>No envíes dinero por adelantado</li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php
include __DIR__ . '/../includes/footer.php';
?>