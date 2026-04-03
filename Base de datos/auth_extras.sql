-- ============================================================
-- MotoSpot — Tablas y columnas para Auth extras
-- Ejecutar una sola vez en phpMyAdmin
-- ============================================================

-- Tabla de tokens de recuperación de contraseña
CREATE TABLE IF NOT EXISTS `ms_password_resets` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(255) NOT NULL,
    `token`      VARCHAR(100) NOT NULL,
    `expires_at` DATETIME     NOT NULL,
    `used`       TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_token` (`token`),
    INDEX `idx_email`   (`email`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Columna google_id para usuarios que se registran con Google OAuth
ALTER TABLE `ms_usuarios`
    ADD COLUMN IF NOT EXISTS `google_id`     VARCHAR(100) DEFAULT NULL AFTER `email`,
    ADD COLUMN IF NOT EXISTS `avatar_url`    VARCHAR(500) DEFAULT NULL AFTER `google_id`,
    ADD COLUMN IF NOT EXISTS `auth_provider` ENUM('local','google') NOT NULL DEFAULT 'local' AFTER `avatar_url`;

-- Índice para buscar por google_id
ALTER TABLE `ms_usuarios`
    ADD INDEX IF NOT EXISTS `idx_google_id` (`google_id`);
