<?php
/**
 * MotoSpot - Navbar
 * Barra de navegación principal - Dark Mode
 * 
 * @author Kevin
 * @version 2.0.0
 */

// Asegurar que auth.php esté cargado
if (!function_exists('estaAutenticado')) {
    require_once __DIR__ . '/auth.php';
}

$usuarioNombre = $_SESSION['usuario_nombre'] ?? '';
$usuarioFoto = $_SESSION['usuario_foto'] ?? '';
$esAgencia = esAgencia();
?>

<!-- Navbar Principal -->
<nav class="navbar">
    <div class="navbar-container">
        <!-- Logo -->
        <a href="/index.php" class="navbar-brand" style="font-weight: 800; letter-spacing: -0.5px;">
            <span style="color: #fff;">MOTO</span><span style="color: #3ABBE5;">SPOT</span>
        </a>
        
        <!-- Menú Principal Desktop -->
        <div class="navbar-menu" id="navbarMenu">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="/index.php" class="nav-link">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/listado-vehiculos.php" class="nav-link">
                        <i class="fas fa-search"></i> Buscar Vehículos
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/embarcaciones.php" class="nav-link">
                        <i class="fas fa-ship"></i> Embarcaciones
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/planes.php" class="nav-link">
                        <i class="fas fa-crown"></i> Planes
                    </a>
                </li>
                
                <?php if (estaAutenticado()): ?>
                    <li class="nav-item">
                        <a href="/publicar-vehiculo.php" class="nav-link nav-link-highlight">
                            <i class="fas fa-plus-circle"></i> Publicar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        
        <!-- Menú de Usuario -->
        <div class="navbar-user">
            <?php if (estaAutenticado()): ?>
                <div class="dropdown">
                    <button class="dropdown-toggle" onclick="toggleDropdown()">
                        <?php if ($usuarioFoto): ?>
                            <img src="<?php echo htmlspecialchars($usuarioFoto); ?>" alt="Perfil" class="user-avatar">
                        <?php else: ?>
                            <div class="user-avatar user-avatar-default">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <span class="user-name"><?php echo htmlspecialchars($usuarioNombre); ?></span>
                        <i class="fas fa-chevron-down" style="color: var(--color-text-muted); font-size: 0.75rem;"></i>
                    </button>
                    <div class="dropdown-menu" id="userDropdown">
                        <a href="/perfil.php" class="dropdown-item">
                            <i class="fas fa-user-circle"></i> Mi Perfil
                        </a>
                        <a href="/publicar-vehiculo.php" class="dropdown-item">
                            <i class="fas fa-plus"></i> Publicar Vehículo
                        </a>
                        <a href="/mis-publicaciones.php" class="dropdown-item">
                            <i class="fas fa-car"></i> Mis Publicaciones
                        </a>
                        <a href="/planes.php" class="dropdown-item">
                            <i class="fas fa-crown"></i> Mi Plan
                        </a>
                        <hr class="dropdown-divider">
                        <a href="/logout.php" class="dropdown-item dropdown-item-danger">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="/login.php" class="btn btn-outline">Iniciar Sesión</a>
                    <a href="/register.php" class="btn btn-primary">Registrarse</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Botón Menú Móvil -->
        <button class="navbar-toggler" onclick="toggleMobileMenu()" aria-label="Menú">
            <span class="toggler-bar"></span>
            <span class="toggler-bar"></span>
            <span class="toggler-bar"></span>
        </button>
    </div>
    
    <!-- Menú Móvil -->
    <div class="mobile-menu" id="mobileMenu">
        <ul class="mobile-nav">
            <li><a href="/index.php"><i class="fas fa-home"></i> Inicio</a></li>
            <li><a href="/listado-vehiculos.php"><i class="fas fa-search"></i> Buscar Vehículos</a></li>
            <li><a href="/embarcaciones.php"><i class="fas fa-ship"></i> Embarcaciones</a></li>
            <li><a href="/planes.php"><i class="fas fa-crown"></i> Planes</a></li>
            
            <?php if (estaAutenticado()): ?>
                <li><a href="/publicar-vehiculo.php"><i class="fas fa-plus-circle"></i> Publicar Vehículo</a></li>
                <li><a href="/perfil.php"><i class="fas fa-user-circle"></i> Mi Perfil</a></li>
                <li><a href="/mis-publicaciones.php"><i class="fas fa-car"></i> Mis Publicaciones</a></li>
                <li><a href="/planes.php"><i class="fas fa-crown"></i> Mi Plan</a></li>
                <li><a href="/logout.php" style="color: #fca5a5;"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
            <?php else: ?>
                <li><a href="/login.php"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a></li>
                <li><a href="/register.php"><i class="fas fa-user-plus"></i> Registrarse</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- Spacer para compensar navbar fijo -->
<div class="navbar-spacer"></div>
