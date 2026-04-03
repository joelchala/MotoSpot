<?php
/**
 * MotoSpot - Publicar Vehículo
 * Formulario para crear nuevas publicaciones
 * 
 * @author Kevin
 * @version 1.0.0
 */

define('MOTO_SPOT', true);
require_once __DIR__ . '/../includes/auth.php';
// db.php ya está incluido por auth.php → no se vuelve a cargar

// Requerir autenticación
requerirAutenticacion('/publicar-vehiculo.php');

$usuario = getUsuarioActual();
$error = '';

// Listas de opciones
$marcas = ['Toyota', 'Honda', 'Hyundai', 'Ford', 'Nissan', 'Kia', 'BMW', 'Mercedes-Benz', 'Audi', 'Volkswagen', 
           'Chevrolet', 'Mazda', 'Subaru', 'Jeep', 'Dodge', 'Chrysler', 'Lexus', 'Infiniti', 'Acura', 'Mitsubishi',
           'Suzuki', 'Peugeot', 'Renault', 'Citroën', 'Fiat', 'Mini', 'Land Rover', 'Jaguar', 'Porsche', 'Volvo'];

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

$transmisiones = [
    'automatica' => 'Automática',
    'manual' => 'Manual',
    'cvt' => 'CVT',
    'semiautomatica' => 'Semi-automática'
];

$combustibles = [
    'gasolina' => 'Gasolina',
    'diesel' => 'Diésel',
    'hibrido' => 'Híbrido',
    'electrico' => 'Eléctrico',
    'gnv' => 'GNV',
    'gpl' => 'GPL'
];

$tracciones = [
    'delantera' => 'Delantera',
    'trasera' => 'Trasera',
    '4x4' => '4x4',
    'awd' => 'AWD'
];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verificarCSRFToken($csrf_token)) {
        $error = 'Token de seguridad inválido. Intente nuevamente.';
    } else {
        // Validar campos obligatorios
        $marca = trim($_POST['marca'] ?? '');
        $modelo = trim($_POST['modelo'] ?? '');
        $ano = intval($_POST['ano'] ?? 0);
        $precio = floatval($_POST['precio'] ?? 0);
        $condicion = $_POST['condicion'] ?? '';
        $tipo_vehiculo = $_POST['tipo_vehiculo'] ?? '';
        $transmision = $_POST['transmision'] ?? '';
        $combustible = $_POST['combustible'] ?? '';
        $traccion = $_POST['traccion'] ?? '';
        $kilometraje = intval($_POST['kilometraje'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $ciudad = trim($_POST['ciudad'] ?? '');
        $provincia = $_POST['provincia'] ?? '';
        
        // Campos opcionales
        $motor = trim($_POST['motor'] ?? '');
        $cilindraje = floatval($_POST['cilindraje'] ?? 0);
        $color_exterior = trim($_POST['color_exterior'] ?? '');
        $color_interior = trim($_POST['color_interior'] ?? '');
        $puertas = intval($_POST['puertas'] ?? 0);
        $pasajeros = intval($_POST['pasajeros'] ?? 0);
        $precio_negociable = isset($_POST['precio_negociable']) ? 1 : 0;
        
        // Validaciones mejoradas
        if (!validarString($marca, 2, 50)) {
            $error = 'La marca debe tener entre 2 y 50 caracteres';
        } elseif (!validarString($modelo, 1, 100)) {
            $error = 'El modelo es obligatorio (máx 100 caracteres)';
        } elseif (!validarAno($ano)) {
            $error = 'El año debe estar entre 1900 y ' . (date('Y') + 1);
        } elseif (!validarFloat($precio, 100)) {
            $error = 'El precio debe ser mínimo $100';
        } elseif (!validarEnum($condicion, ['nuevo' => 'Nuevo', 'usado' => 'Usado'])) {
            $error = 'Seleccione una condición válida';
        } elseif (!validarEnum($tipo_vehiculo, $tiposVehiculo)) {
            $error = 'Seleccione un tipo de vehículo válido';
        } elseif (!validarEnum($transmision, $transmisiones)) {
            $error = 'Seleccione una transmisión válida';
        } elseif (!validarEnum($combustible, $combustibles)) {
            $error = 'Seleccione un combustible válido';
        } elseif (!validarEnum($traccion, $tracciones)) {
            $error = 'Seleccione una tracción válida';
        } elseif (!validarString($titulo, 5, 150)) {
            $error = 'El título debe tener entre 5 y 150 caracteres';
        } elseif (strlen($descripcion) > 2000) {
            $error = 'La descripción no puede exceder 2000 caracteres';
        } elseif (!validarString($ciudad, 2, 100)) {
            $error = 'Ingrese una ciudad válida';
        } elseif (!validarString($provincia, 2, 100)) {
            $error = 'Seleccione una provincia válida';
        } elseif ($kilometraje < 0) {
            $error = 'El kilometraje no puede ser negativo';
        } elseif ($puertas > 0 && $puertas < 2) {
            $error = 'El número de puertas debe ser 0 o mayor a 2';
        } elseif ($pasajeros > 0 && $pasajeros < 2) {
            $error = 'El número de pasajeros debe ser 0 o mayor a 2';
        } else {
        // Insertar vehículo
        $sql = "INSERT INTO ms_vehiculos (
            usuario_id, marca, modelo, ano, precio, condicion, tipo_vehiculo,
            transmision, combustible, traccion, kilometraje, titulo, descripcion,
            ciudad, provincia, motor, cilindraje, color_exterior, color_interior,
            puertas, pasajeros, precio_negociable, fecha_vencimiento
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))";
        
        $params = [
            $usuario['id'], $marca, $modelo, $ano, $precio, $condicion, $tipo_vehiculo,
            $transmision, $combustible, $traccion, $kilometraje, $titulo, $descripcion,
            $ciudad, $provincia, $motor, $cilindraje, $color_exterior, $color_interior,
            $puertas, $pasajeros, $precio_negociable
        ];
        
        try {
            beginTransaction();
            $stmt = executeQuery($sql, $params);
            $vehiculoId = getLastInsertId();
            
            // Procesar fotos si se subieron
            if (!empty($_FILES['fotos']['tmp_name'][0])) {
                $uploadDir = __DIR__ . '/../uploads/vehiculos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fotosSubidas = 0;
                foreach ($_FILES['fotos']['tmp_name'] as $index => $tmpName) {
                    if ($_FILES['fotos']['error'][$index] === UPLOAD_ERR_OK) {
                        $fileName = uniqid() . '_' . basename($_FILES['fotos']['name'][$index]);
                        $targetPath = $uploadDir . $fileName;
                        
                        // Validar tipo de archivo
                        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                        $fileType = mime_content_type($tmpName);
                        
                        if (in_array($fileType, $allowedTypes)) {
                            if (move_uploaded_file($tmpName, $targetPath)) {
                                // Insertar referencia en base de datos
                                $fotoUrl = '/uploads/vehiculos/' . $fileName;
                                $esPrincipal = ($fotosSubidas === 0) ? 1 : 0;
                                
                                $sqlFoto = "INSERT INTO ms_vehiculo_fotos (vehiculo_id, url_foto, orden, es_principal) VALUES (?, ?, ?, ?)";
                                executeQuery($sqlFoto, [$vehiculoId, $fotoUrl, $fotosSubidas, $esPrincipal]);
                                $fotosSubidas++;
                            }
                        }
                    }
                }
            }
            
            commitTransaction();
            
            // Redirigir al detalle del vehículo — exit() obligatorio para no seguir ejecutando
            header("Location: /detalle-vehiculo.php?id=$vehiculoId");
            exit();
            
        } catch (Exception $e) {
            rollbackTransaction();
            $error = 'Error al publicar el vehículo: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Publicar Vehículo';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<section class="publish-section">
    <div class="container">
        <div class="publish-header">
            <h1><i class="fas fa-plus-circle"></i> Publicar Vehículo</h1>
            <p>Completa el formulario con la información de tu vehículo</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form action="/publicar-vehiculo.php" method="POST" enctype="multipart/form-data" class="publish-form" id="publishForm">
            
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= generarCSRFToken() ?>">
            
            <!-- Información Básica -->
            <div class="form-section">
                <h2 class="form-section-title">
                    <span class="section-number">1</span>
                    Información Básica
                </h2>
                
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label for="marca">Marca *</label>
                        <select name="marca" id="marca" class="form-control" required>
                            <option value="">Selecciona marca...</option>
                            <?php sort($marcas); foreach ($marcas as $m): ?>
                                <option value="<?php echo $m; ?>"><?php echo $m; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group form-group-half">
                        <label for="modelo">Modelo *</label>
                        <input type="text" name="modelo" id="modelo" class="form-control" 
                               placeholder="Ej: Corolla" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group form-group-third">
                        <label for="ano">Año *</label>
                        <select name="ano" id="ano" class="form-control" required>
                            <option value="">Año...</option>
                            <?php for ($i = date('Y') + 1; $i >= 1950; $i--): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group form-group-third">
                        <label for="precio">Precio (USD) *</label>
                        <input type="number" name="precio" id="precio" class="form-control" 
                               placeholder="Ej: 15000" min="1" required>
                    </div>
                    <div class="form-group form-group-third">
                        <label class="checkbox-label checkbox-price">
                            <input type="checkbox" name="precio_negociable" id="precio_negociable">
                            <span class="checkmark"></span>
                            Precio negociable
                        </label>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label for="condicion">Condición *</label>
                        <select name="condicion" id="condicion" class="form-control" required>
                            <option value="">Selecciona...</option>
                            <option value="nuevo">Nuevo (0km)</option>
                            <option value="seminuevo">Seminuevo</option>
                            <option value="usado">Usado</option>
                        </select>
                    </div>
                    <div class="form-group form-group-half">
                        <label for="tipo_vehiculo">Tipo de Vehículo *</label>
                        <select name="tipo_vehiculo" id="tipo_vehiculo" class="form-control" required>
                            <option value="">Selecciona...</option>
                            <?php foreach ($tiposVehiculo as $key => $value): ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Especificaciones Técnicas -->
            <div class="form-section">
                <h2 class="form-section-title">
                    <span class="section-number">2</span>
                    Especificaciones Técnicas
                </h2>
                
                <div class="form-row">
                    <div class="form-group form-group-third">
                        <label for="transmision">Transmisión *</label>
                        <select name="transmision" id="transmision" class="form-control" required>
                            <option value="">Selecciona...</option>
                            <?php foreach ($transmisiones as $key => $value): ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group form-group-third">
                        <label for="combustible">Combustible *</label>
                        <select name="combustible" id="combustible" class="form-control" required>
                            <option value="">Selecciona...</option>
                            <?php foreach ($combustibles as $key => $value): ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group form-group-third">
                        <label for="traccion">Tracción *</label>
                        <select name="traccion" id="traccion" class="form-control" required>
                            <option value="">Selecciona...</option>
                            <?php foreach ($tracciones as $key => $value): ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label for="kilometraje">Kilometraje *</label>
                        <input type="number" name="kilometraje" id="kilometraje" class="form-control" 
                               placeholder="Ej: 25000" min="0" required>
                    </div>
                    <div class="form-group form-group-half">
                        <label for="motor">Motor</label>
                        <input type="text" name="motor" id="motor" class="form-control" 
                               placeholder="Ej: 2.0L 4 cilindros">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group form-group-third">
                        <label for="cilindraje">Cilindraje (L)</label>
                        <input type="number" name="cilindraje" id="cilindraje" class="form-control" 
                               placeholder="Ej: 2.0" step="0.1" min="0">
                    </div>
                    <div class="form-group form-group-third">
                        <label for="puertas">Puertas</label>
                        <select name="puertas" id="puertas" class="form-control">
                            <option value="">Selecciona...</option>
                            <?php for ($i = 2; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group form-group-third">
                        <label for="pasajeros">Pasajeros</label>
                        <select name="pasajeros" id="pasajeros" class="form-control">
                            <option value="">Selecciona...</option>
                            <?php for ($i = 2; $i <= 9; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label for="color_exterior">Color Exterior</label>
                        <input type="text" name="color_exterior" id="color_exterior" class="form-control" 
                               placeholder="Ej: Blanco">
                    </div>
                    <div class="form-group form-group-half">
                        <label for="color_interior">Color Interior</label>
                        <input type="text" name="color_interior" id="color_interior" class="form-control" 
                               placeholder="Ej: Negro">
                    </div>
                </div>
            </div>
            
            <!-- Descripción -->
            <div class="form-section">
                <h2 class="form-section-title">
                    <span class="section-number">3</span>
                    Descripción y Detalles
                </h2>
                
                <div class="form-group">
                    <label for="titulo">Título de la Publicación *</label>
                    <input type="text" name="titulo" id="titulo" class="form-control" 
                           placeholder="Ej: Toyota Corolla 2022 - Seminuevo, único dueño" maxlength="200" required>
                    <small class="form-text">Máximo 200 caracteres. Sé claro y conciso.</small>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción Detallada</label>
                    <textarea name="descripcion" id="descripcion" class="form-control" rows="6" 
                              placeholder="Describe el vehículo: estado general, mantenimiento, equipamiento especial, razón de venta, etc."></textarea>
                </div>
            </div>
            
            <!-- Ubicación -->
            <div class="form-section">
                <h2 class="form-section-title">
                    <span class="section-number">4</span>
                    Ubicación
                </h2>
                
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label for="ciudad">Ciudad *</label>
                        <input type="text" name="ciudad" id="ciudad" class="form-control" 
                               placeholder="Ej: Santo Domingo" required>
                    </div>
                    <div class="form-group form-group-half">
                        <label for="provincia">Provincia *</label>
                        <select name="provincia" id="provincia" class="form-control" required>
                            <option value="">Selecciona provincia...</option>
                            <option value="Distrito Nacional">Distrito Nacional</option>
                            <option value="Santiago">Santiago</option>
                            <option value="Santo Domingo">Santo Domingo</option>
                            <option value="La Romana">La Romana</option>
                            <option value="San Pedro de Macorís">San Pedro de Macorís</option>
                            <option value="Puerto Plata">Puerto Plata</option>
                            <option value="La Vega">La Vega</option>
                            <option value="San Cristóbal">San Cristóbal</option>
                            <option value="Barahona">Barahona</option>
                            <option value="San Juan">San Juan</option>
                            <option value="Duarte">Duarte</option>
                            <option value="María Trinidad Sánchez">María Trinidad Sánchez</option>
                            <option value="Otra">Otra</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Fotos -->
            <div class="form-section">
                <h2 class="form-section-title">
                    <span class="section-number">5</span>
                    Fotos del Vehículo
                </h2>
                
                <div class="form-group">
                    <label for="fotos">Subir Fotos</label>
                    <div class="file-upload" id="fileUpload">
                        <input type="file" name="fotos[]" id="fotos" multiple accept="image/*" 
                               class="file-input" onchange="previewImages(this)">
                        <div class="file-upload-content">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Arrastra fotos aquí o haz clic para seleccionar</p>
                            <small>Máximo 10 fotos (JPG, PNG). La primera será la principal.</small>
                        </div>
                    </div>
                    <div class="image-preview" id="imagePreview"></div>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="/index.php" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary btn-large">
                    <i class="fas fa-check-circle"></i> Publicar Vehículo
                </button>
            </div>
        </form>
    </div>
</section>

<?php
include __DIR__ . '/../includes/footer.php';
?>