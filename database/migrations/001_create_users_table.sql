-- ============================================================
-- Migration 001 — Create users table
-- Stores admin CMS accounts.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(100)        NOT NULL,
    `email`         VARCHAR(150)        NOT NULL,
    `password_hash` VARCHAR(255)        NOT NULL,
    `role`          ENUM('admin','editor') NOT NULL DEFAULT 'admin',
    `avatar`        VARCHAR(500)        NULL DEFAULT NULL,
    `last_login_at` DATETIME            NULL DEFAULT NULL,
    `is_active`     TINYINT(1)          NOT NULL DEFAULT 1,
    `created_at`    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_active` (`is_active`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Admin CMS user accounts';

SET FOREIGN_KEY_CHECKS = 1;
