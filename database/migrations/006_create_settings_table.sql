-- ============================================================
-- Migration 006 — Create settings table
-- Key-value store for all site-wide configuration.
-- Grouped by: general | seo | social | appearance | robots | mail
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `settings` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `key`         VARCHAR(100)    NOT NULL COMMENT 'Dot-free key e.g. site_name, seo_default_title',
    `value`       LONGTEXT        NULL DEFAULT NULL,
    `type`        ENUM('string','boolean','integer','json','text') NOT NULL DEFAULT 'string'
                                  COMMENT 'Hint for casting on read',
    `group`       VARCHAR(50)     NOT NULL DEFAULT 'general'
                                  COMMENT 'Tab grouping in admin Settings UI',
    `label`       VARCHAR(150)    NULL DEFAULT NULL COMMENT 'Human-readable label for admin UI',
    `description` VARCHAR(300)    NULL DEFAULT NULL COMMENT 'Helper text shown in admin UI',
    `is_public`   TINYINT(1)      NOT NULL DEFAULT 0
                                  COMMENT '1 = safe to expose in frontend JS config',
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_settings_key` (`key`),
    KEY `idx_settings_group` (`group`),
    KEY `idx_settings_public` (`is_public`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Site-wide key-value configuration store';

SET FOREIGN_KEY_CHECKS = 1;
