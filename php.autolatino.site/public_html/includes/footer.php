<?php
/**
 * MotoSpot - Footer
 * Pie de página común - Dark Mode
 * 
 * @author Kevin
 * @version 2.0.0
 */
?>
    </div><!-- /.page-wrapper -->
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-grid">
                <!-- Columna 1: Sobre Nosotros -->
                <div class="footer-column">
                    <h4 class="footer-title">
                        <i class="fas fa-car-side"></i> MotoSpot
                    </h4>
                    <p class="footer-description">
                        La plataforma líder y más confiable para la compra y venta de vehículos y embarcaciones en Argentina. Encontrá el auto, moto o lancha de tus sueños o vendé el tuyo al mejor precio.
                    </p>
                    <div class="footer-social">
                        <a href="#" class="social-link" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Columna 2: Vehículos -->
                <div class="footer-column">
                    <h5 class="footer-subtitle">Vehículos</h5>
                    <ul class="footer-links">
                        <li><a href="/index.php">Inicio</a></li>
                        <li><a href="/listado-vehiculos.php">Buscar Vehículos</a></li>
                        <li><a href="/listado-vehiculos.php?tipo=suv">SUVs</a></li>
                        <li><a href="/listado-vehiculos.php?tipo=sedan">Sedanes</a></li>
                        <li><a href="/listado-vehiculos.php?tipo=pickup">Pickups</a></li>
                        <li><a href="/publicar-vehiculo.php">Vender mi Auto</a></li>
                    </ul>
                </div>
                
                <!-- Columna 3: Embarcaciones -->
                <div class="footer-column">
                    <h5 class="footer-subtitle"><i class="fas fa-ship"></i> Embarcaciones</h5>
                    <ul class="footer-links">
                        <li><a href="/embarcaciones.php">Ver embarcaciones</a></li>
                        <li><a href="/embarcaciones.php?tipo=jet-ski">Jet Ski</a></li>
                        <li><a href="/embarcaciones.php?tipo=lancha">Lanchas</a></li>
                        <li><a href="/embarcaciones.php?tipo=yate">Yates</a></li>
                        <li><a href="/publicar-embarcacion.php">Publicar embarcación</a></li>
                    </ul>
                </div>
                
                <!-- Columna 4: Soporte -->
                <div class="footer-column">
                    <h5 class="footer-subtitle">Soporte</h5>
                    <ul class="footer-links">
                        <li><a href="/planes.php">Planes</a></li>
                        <li><a href="#">Ayuda</a></li>
                        <li><a href="#">Términos y Condiciones</a></li>
                        <li><a href="#">Política de Privacidad</a></li>
                        <li><a href="#">Contacto</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> MotoSpot. Todos los derechos reservados.</p>
                <div class="footer-legal">
                    <a href="#">Términos y Condiciones</a>
                    <a href="#">Política de Privacidad</a>
                    <a href="#">Cookies</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Toast Notifications Container -->
    <div id="toast-container" class="toast-container"></div>
    
    <!-- JavaScript Principal -->
    <script src="/assets/js/app.js"></script>
    
    <?php if (isset($extraJS)): ?>
        <?php echo $extraJS; ?>
    <?php endif; ?>
</body>
</html>
