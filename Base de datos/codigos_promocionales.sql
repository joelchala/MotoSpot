--
-- Tabla para códigos promocionales
-- Solo aplican para el plan Premium Plus (dealers)
-- Duración: 1 mes (30 días)
-- Un solo canje por código
--

CREATE TABLE IF NOT EXISTS `ms_codigos_promocionales` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `codigo` VARCHAR(32) NOT NULL UNIQUE,
    `plan_destino` VARCHAR(50) NOT NULL DEFAULT 'premium_plus',
    `duracion_dias` INT UNSIGNED NOT NULL DEFAULT 30,
    `usado` TINYINT(1) NOT NULL DEFAULT 0,
    `usado_por` INT UNSIGNED DEFAULT NULL,
    `usado_en` DATETIME DEFAULT NULL,
    `creado_por` INT UNSIGNED DEFAULT NULL,
    `creado_en` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `fecha_inicio` DATE DEFAULT NULL COMMENT 'Fecha desde la que empieza a regir el plan canjeado',
    `fecha_expiracion` DATE DEFAULT NULL COMMENT 'Fecha de expiración del código si no se usa',
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `notas` TEXT DEFAULT NULL,
    INDEX `idx_codigo` (`codigo`),
    INDEX `idx_usado` (`usado`),
    INDEX `idx_activo` (`activo`),
    FOREIGN KEY (`usado_por`) REFERENCES `ms_usuarios`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`creado_por`) REFERENCES `ms_usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para historial de canjes de códigos
CREATE TABLE IF NOT EXISTS `ms_historial_codigos` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `codigo_id` INT UNSIGNED NOT NULL,
    `usuario_id` INT UNSIGNED NOT NULL,
    `codigo` VARCHAR(32) NOT NULL,
    `plan_asignado` VARCHAR(50) NOT NULL,
    `duracion_dias` INT UNSIGNED NOT NULL,
    `fecha_inicio` DATE NOT NULL,
    `fecha_fin` DATE NOT NULL,
    `canjeado_en` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_usuario` (`usuario_id`),
    INDEX `idx_fechas` (`fecha_inicio`, `fecha_fin`),
    FOREIGN KEY (`codigo_id`) REFERENCES `ms_codigos_promocionales`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`usuario_id`) REFERENCES `ms_usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar campo codigo_promo_usado a ms_usuarios si no existe
ALTER TABLE `ms_usuarios` 
ADD COLUMN IF NOT EXISTS `codigo_promo_activo` TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `codigo_promo_hasta` DATE DEFAULT NULL;
