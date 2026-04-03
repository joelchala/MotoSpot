-- ============================================================
-- MotoSpot — Tabla: ms_email_queue
-- Cola de emails para envío asíncrono vía cron
-- Ejecutar una sola vez en phpMyAdmin o HeidiSQL
-- ============================================================

CREATE TABLE IF NOT EXISTS `ms_email_queue` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `to_email`      VARCHAR(255)     NOT NULL,
    `to_name`       VARCHAR(255)     NOT NULL DEFAULT '',
    `subject`       VARCHAR(500)     NOT NULL,
    `body_html`     MEDIUMTEXT,
    `body_text`     MEDIUMTEXT,
    `status`        ENUM('pending','processing','sent','failed')
                                     NOT NULL DEFAULT 'pending',
    `retries`       TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `next_retry_at` DATETIME                  DEFAULT NULL,
    `sent_at`       DATETIME                  DEFAULT NULL,
    `created_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `metadata`      JSON                       DEFAULT NULL  COMMENT 'Datos extra: vehiculo_id, tipo_evento, etc.',

    PRIMARY KEY (`id`),
    INDEX `idx_status_retry` (`status`, `next_retry_at`),
    INDEX `idx_created`      (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Cola de emails para envío asíncrono';
