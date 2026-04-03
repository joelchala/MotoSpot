<?php
/**
 * MotoSpot - Admin Panel - Códigos Promocionales
 * Panel de administración para gestionar códigos promocionales
 * 
 * @author Kevin
 * @version 1.0.0
 */

define('MOTO_SPOT', true);

// Cargar variables de entorno
require_once __DIR__ . '/../includes/env.php';
loadEnv();

// Incluir autenticación y funciones de códigos
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/codigos_promocionales.php';

// Verificar que sea administrador
if (!esAdmin()) {
    header('Location: /login.php');
    exit();
}

// Procesar POST para generar código
$success = false;
$error = false;
$codigoGenerado = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generar_codigo'])) {
    // Verificar CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verificarCSRFToken($csrf_token)) {
        $error = true;
        $message = 'Token de seguridad inválido. Intente nuevamente.';
    } else {
        $notas = htmlspecialchars($_POST['notas'] ?? '', ENT_QUOTES, 'UTF-8');
        
        $resultado = generarCodigoPromocional($_SESSION['usuario_id'], $notas);
        
        if ($resultado['success']) {
            $success = true;
            $codigoGenerado = $resultado['codigo'];
            $message = $resultado['message'];
        } else {
            $error = true;
            $message = $resultado['message'];
        }
    }
}

// Obtener todos los códigos
$codigos = obtenerCodigosPromocionales();

// Configurar título de la página
$pageTitle = 'Admin - Códigos Promocionales';

// Incluir header y navbar
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<style>
.admin-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.admin-header {
    margin-bottom: 2rem;
}

.admin-title {
    font-size: 1.875rem;
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.admin-title i {
    color: #3ABBE5;
}

.admin-card {
    background: #16162a;
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #252542;
}

.admin-card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #fff;
    margin-bottom: 1rem;
}

/* Formulario */
.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #a0aec0;
    margin-bottom: 0.5rem;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    background: #0d0d1a;
    border: 1px solid #252542;
    border-radius: 0.5rem;
    color: #fff;
    font-size: 0.9375rem;
    transition: all 0.2s ease;
}

.form-input:focus {
    outline: none;
    border-color: #3ABBE5;
    box-shadow: 0 0 0 3px rgba(58, 187, 229, 0.1);
}

.form-input::placeholder {
    color: #64748b;
}

.btn-primary {
    background: linear-gradient(135deg, #3ABBE5 0%, #2a9bc2 100%);
    color: #fff;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.9375rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(58, 187, 229, 0.3);
}

/* Alertas */
.alert {
    padding: 1rem 1.25rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-success {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.2);
    color: #4ade80;
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
    color: #fca5a5;
}

.alert-code {
    font-family: 'Courier New', monospace;
    font-weight: 700;
    font-size: 1.1em;
    background: rgba(0,0,0,0.3);
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    letter-spacing: 1px;
}

/* Tabla */
.table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    text-align: left;
    padding: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    background: #0d0d1a;
    border-bottom: 1px solid #252542;
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid #252542;
    color: #e2e8f0;
    font-size: 0.875rem;
}

.data-table tr:hover td {
    background: rgba(58, 187, 229, 0.05);
}

.code-cell {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #3ABBE5;
    font-size: 0.9375rem;
    letter-spacing: 1px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-active {
    background: rgba(34, 197, 94, 0.1);
    color: #4ade80;
}

.status-used {
    background: rgba(239, 68, 68, 0.1);
    color: #fca5a5;
}

.status-inactive {
    background: rgba(100, 116, 139, 0.1);
    color: #94a3b8;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #64748b;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-container {
        padding: 1rem;
    }
    
    .admin-title {
        font-size: 1.5rem;
    }
    
    .hide-mobile {
        display: none;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.75rem 0.5rem;
        font-size: 0.8125rem;
    }
    
    .code-cell {
        font-size: 0.8125rem;
    }
}
</style>

<div class="admin-container">
    <div class="admin-header">
        <h1 class="admin-title">
            <i class="fas fa-ticket-alt"></i>
            Administración de Códigos Promocionales
        </h1>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <div>
            <strong>¡Código generado exitosamente!</strong><br>
            Código: <span class="alert-code"><?php echo htmlspecialchars($codigoGenerado); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            <strong>Error:</strong> <?php echo htmlspecialchars($message); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Formulario para generar código -->
    <div class="admin-card">
        <h2 class="admin-card-title">
            <i class="fas fa-plus-circle" style="color: #3ABBE5;"></i>
            Generar Nuevo Código
        </h2>
        <form method="POST" action="">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= generarCSRFToken() ?>">
            
            <div class="form-group">
                <label for="notas" class="form-label">Notas (opcional)</label>
                <input type="text" 
                       id="notas" 
                       name="notas" 
                       class="form-input" 
                       placeholder="Ej: Campaña Facebook, Dealer XYZ, etc."
                       maxlength="255">
            </div>
            <button type="submit" name="generar_codigo" class="btn-primary">
                <i class="fas fa-magic"></i>
                Generar Código
            </button>
        </form>
    </div>

    <!-- Tabla de códigos -->
    <div class="admin-card">
        <h2 class="admin-card-title">
            <i class="fas fa-list" style="color: #3ABBE5;"></i>
            Códigos Generados
            <span style="float: right; font-size: 0.875rem; color: #64748b; font-weight: normal;">
                Total: <?php echo count($codigos); ?>
            </span>
        </h2>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Estado</th>
                        <th class="hide-mobile">Usado por</th>
                        <th class="hide-mobile">Fecha uso</th>
                        <th class="hide-mobile">Notas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($codigos)): ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="fas fa-ticket-alt"></i>
                                <p>No hay códigos promocionales generados</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($codigos as $codigo): ?>
                        <tr>
                            <td class="code-cell"><?php echo htmlspecialchars($codigo['codigo']); ?></td>
                            <td>
                                <?php if ($codigo['usado']): ?>
                                    <span class="status-badge status-used">
                                        <i class="fas fa-times-circle"></i>
                                        Usado
                                    </span>
                                <?php elseif (!$codigo['activo']): ?>
                                    <span class="status-badge status-inactive">
                                        <i class="fas fa-ban"></i>
                                        Inactivo
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-active">
                                        <i class="fas fa-check-circle"></i>
                                        Activo
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="hide-mobile">
                                <?php echo $codigo['usado_por_nombre'] ? htmlspecialchars($codigo['usado_por_nombre']) : '-'; ?>
                            </td>
                            <td class="hide-mobile">
                                <?php echo $codigo['usado_en'] ? date('d/m/Y H:i', strtotime($codigo['usado_en'])) : '-'; ?>
                            </td>
                            <td class="hide-mobile">
                                <?php echo $codigo['notas'] ? htmlspecialchars($codigo['notas']) : '-'; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
