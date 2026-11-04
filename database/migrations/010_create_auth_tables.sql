-- ============================================================
-- Migration 010 — Auth support tables
-- password_resets: secure token-based reset flow
-- login_attempts: brute-force rate limiting
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------
-- password_resets
-- One row per pending reset request. Expires after 60 min.
-- Token is a SHA-256 hex of a random 32-byte secret.
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(150) NOT NULL,
    `token_hash` VARCHAR(64)  NOT NULL COMMENT 'SHA-256 hex of the raw token sent to user',
    `expires_at` DATETIME     NOT NULL,
    `used_at`    DATETIME     NULL DEFAULT NULL COMMENT 'Set when token is consumed',
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_pr_email`      (`email`),
    KEY `idx_pr_token_hash` (`token_hash`),
    KEY `idx_pr_expires`    (`expires_at`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Password reset token store';

-- ----------------------------------------------------------
-- login_attempts
-- Tracks failed logins per email for brute-force blocking.
-- Cleared automatically by cleanup logic after 15 min.
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(150) NOT NULL,
    `ip`         VARCHAR(45)  NOT NULL,
    `attempted_at` DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_la_email`   (`email`),
    KEY `idx_la_ip`      (`ip`),
    KEY `idx_la_time`    (`attempted_at`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Failed login attempt log for rate limiting';

SET FOREIGN_KEY_CHECKS = 1;
