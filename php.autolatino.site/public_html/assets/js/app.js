/**
 * MotoSpot - JavaScript Principal
 * Portal Automotriz
 * 
 * @author Kevin
 * @version 1.0.0
 */

// =====================================================
// UTILIDADES
// =====================================================

/**
 * Muestra un mensaje toast
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo de mensaje (success, error, info)
 * @param {number} duration - Duración en ms
 */
function showToast(message, type = 'success', duration = 3000) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

/**
 * Alternar visibilidad de contraseña
 * @param {string} inputId - ID del input
 */
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.parentElement.querySelector('.toggle-password');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

/**
 * Alternar dropdown del usuario
 */
function toggleDropdown() {
    const dropdown = document.querySelector('.dropdown');
    if (dropdown) {
        dropdown.classList.toggle('active');
    }
}

/**
 * Alternar menú móvil
 */
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    if (menu) {
        menu.classList.toggle('active');
    }
}

/**
 * Alternar sidebar de filtros
 */
function toggleFilters() {
    const sidebar = document.getElementById('filtersSidebar');
    const overlay = document.getElementById('filtersOverlay');
    
    if (sidebar && overlay) {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
    }
}

// Cerrar dropdown al hacer click fuera
document.addEventListener('click', function(e) {
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        if (!dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });
});

// =====================================================
// VALIDACIÓN DE FORMULARIOS
// =====================================================

/**
 * Validar email
 * @param {string} email
 * @returns {boolean}
 */
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validar formulario de login
 */
function validateLoginForm() {
    const form = document.getElementById('loginForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        
        if (!email) {
            e.preventDefault();
            showToast('Por favor, ingresa tu correo electrónico', 'error');
            return;
        }
        
        if (!isValidEmail(email)) {
            e.preventDefault();
            showToast('Por favor, ingresa un correo electrónico válido', 'error');
            return;
        }
        
        if (!password) {
            e.preventDefault();
            showToast('Por favor, ingresa tu contraseña', 'error');
            return;
        }
    });
}

/**
 * Validar formulario de registro
 */
function validateRegisterForm() {
    const form = document.getElementById('registerForm');
    if (!form) return;
    
    // Cambiar campos según tipo de cuenta
    const tipoInputs = document.querySelectorAll('input[name="tipo"]');
    const agenciaFields = document.getElementById('agencia-fields');
    
    tipoInputs.forEach(input => {
        input.addEventListener('change', function() {
            const tipoOptions = document.querySelectorAll('.account-type-option');
            tipoOptions.forEach(opt => opt.classList.remove('active'));
            this.closest('.account-type-option').classList.add('active');
            
            if (agenciaFields) {
                agenciaFields.style.display = this.value === 'agencia' ? 'block' : 'none';
            }
        });
    });
    
    form.addEventListener('submit', function(e) {
        const nombre = document.getElementById('nombre').value.trim();
        const email = document.getElementById('email').value.trim();
        const telefono = document.getElementById('telefono').value.trim();
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirm').value;
        const terminos = document.getElementById('terminos').checked;
        const tipo = document.querySelector('input[name="tipo"]:checked')?.value;
        
        if (!nombre) {
            e.preventDefault();
            showToast('El nombre es obligatorio', 'error');
            return;
        }
        
        if (!email || !isValidEmail(email)) {
            e.preventDefault();
            showToast('Por favor, ingresa un correo electrónico válido', 'error');
            return;
        }
        
        if (!telefono) {
            e.preventDefault();
            showToast('El teléfono es obligatorio', 'error');
            return;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            showToast('La contraseña debe tener al menos 6 caracteres', 'error');
            return;
        }
        
        if (password !== passwordConfirm) {
            e.preventDefault();
            showToast('Las contraseñas no coinciden', 'error');
            return;
        }
        
        if (!terminos) {
            e.preventDefault();
            showToast('Debes aceptar los términos y condiciones', 'error');
            return;
        }
        
        if (tipo === 'agencia') {
            const nombreAgencia = document.getElementById('nombre_agencia').value.trim();
            if (!nombreAgencia) {
                e.preventDefault();
                showToast('El nombre de la agencia es obligatorio', 'error');
                return;
            }
        }
    });
}

/**
 * Validar formulario de publicación
 */
function validatePublishForm() {
    const form = document.getElementById('publishForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        const marca = document.getElementById('marca').value;
        const modelo = document.getElementById('modelo').value.trim();
        const ano = document.getElementById('ano').value;
        const precio = document.getElementById('precio').value;
        const titulo = document.getElementById('titulo').value.trim();
        
        if (!marca) {
            e.preventDefault();
            showToast('Por favor, selecciona una marca', 'error');
            return;
        }
        
        if (!modelo) {
            e.preventDefault();
            showToast('Por favor, ingresa el modelo', 'error');
            return;
        }
        
        if (!ano) {
            e.preventDefault();
            showToast('Por favor, selecciona el año', 'error');
            return;
        }
        
        if (!precio || precio <= 0) {
            e.preventDefault();
            showToast('Por favor, ingresa un precio válido', 'error');
            return;
        }
        
        if (!titulo) {
            e.preventDefault();
            showToast('Por favor, ingresa un título para la publicación', 'error');
            return;
        }
    });
}

// =====================================================
// GALERÍA DE IMÁGENES
// =====================================================

/**
 * Cambiar imagen principal en la galería
 * @param {HTMLElement} thumbnail - Elemento thumbnail clickeado
 */
function changeMainImage(thumbnail) {
    const mainImage = document.getElementById('mainImage');
    if (!mainImage) return;
    
    // Actualizar imagen principal
    mainImage.src = thumbnail.src;
    
    // Actualizar clase activa
    const thumbnails = document.querySelectorAll('.gallery-thumbnails img');
    thumbnails.forEach(t => t.classList.remove('active'));
    thumbnail.classList.add('active');
}

/**
 * Previsualizar imágenes antes de subir
 * @param {HTMLInputElement} input - Input de archivo
 */
function previewImages(input) {
    const preview = document.getElementById('imagePreview');
    if (!preview) return;
    
    preview.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
        if (input.files.length > 10) {
            showToast('Máximo 10 fotos permitidas', 'error');
            input.value = '';
            return;
        }
        
        Array.from(input.files).forEach((file, index) => {
            if (!file.type.startsWith('image/')) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview ${index + 1}">
                    ${index === 0 ? '<span class="preview-badge">Principal</span>' : ''}
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
}

// =====================================================
// FAVORITOS
// =====================================================

/**
 * Alternar vehículo favorito
 * @param {number} vehiculoId - ID del vehículo
 */
function toggleFavorito(vehiculoId) {
    // Verificar si el usuario está autenticado
    const isAuthenticated = document.body.dataset.authenticated === 'true';
    
    if (!isAuthenticated) {
        showToast('Debes iniciar sesión para agregar favoritos', 'error');
        setTimeout(() => {
            window.location.href = '/login.php';
        }, 1500);
        return;
    }
    
    const button = event.currentTarget;
    const icon = button.querySelector('i');
    const isActive = button.classList.contains('active');
    
    // Simular llamada AJAX (en producción sería una petición real)
    fetch('/api/favoritos.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            vehiculo_id: vehiculoId,
            action: isActive ? 'remove' : 'add'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.toggle('active');
            icon.classList.toggle('far');
            icon.classList.toggle('fas');
            showToast(isActive ? 'Eliminado de favoritos' : 'Agregado a favoritos', 'success');
        }
    })
    .catch(() => {
        // Fallback si no existe el endpoint
        button.classList.toggle('active');
        icon.classList.toggle('far');
        icon.classList.toggle('fas');
        showToast(isActive ? 'Eliminado de favoritos' : 'Agregado a favoritos', 'success');
    });
}

// =====================================================
// COMPARTIR
// =====================================================

/**
 * Compartir vehículo
 */
function compartir() {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            url: window.location.href
        }).catch(() => {
            // Usuario canceló
        });
    } else {
        // Fallback: copiar URL al portapapeles
        navigator.clipboard.writeText(window.location.href).then(() => {
            showToast('Enlace copiado al portapapeles', 'success');
        });
    }
}

// =====================================================
// ORDENAMIENTO
// =====================================================

/**
 * Ordenar resultados
 * @param {string} sortBy - Criterio de ordenamiento
 */
function sortResults(sortBy) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', sortBy);
    window.location.href = url.toString();
}

// =====================================================
// DRAG AND DROP PARA FOTOS
// =====================================================

/**
 * Inicializar drag and drop para upload de fotos
 */
function initDragAndDrop() {
    const dropZone = document.getElementById('fileUpload');
    const fileInput = document.getElementById('fotos');
    
    if (!dropZone || !fileInput) return;
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('drag-over');
        }, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('drag-over');
        }, false);
    });
    
    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        previewImages(fileInput);
    }, false);
    
    dropZone.addEventListener('click', () => {
        fileInput.click();
    });
}

// =====================================================
// INICIALIZACIÓN
// =====================================================

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar validaciones
    validateLoginForm();
    validateRegisterForm();
    validatePublishForm();
    
    // Inicializar drag and drop
    initDragAndDrop();
    
    // Inicializar otros componentes
    console.log('MotoSpot - Portal Automotriz cargado exitosamente');
});

// =====================================================
// UTILIDADES ADICIONALES
// =====================================================

/**
 * Formatear precio
 * @param {number} price
 * @returns {string}
 */
function formatPrice(price) {
    return new Intl.NumberFormat('es-DO', {
        style: 'currency',
        currency: 'USD'
    }).format(price);
}

/**
 * Formatear número con separadores
 * @param {number} num
 * @returns {string}
 */
function formatNumber(num) {
    return new Intl.NumberFormat('es-DO').format(num);
}

/**
 * Debounce para eventos
 * @param {Function} func
 * @param {number} wait
 * @returns {Function}
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle para eventos
 * @param {Function} func
 * @param {number} limit
 * @returns {Function}
 */
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}
