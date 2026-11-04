-- ============================================================
-- Migration 003 — Create media table
-- Central media library. Referenced by posts, projects, pages.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `media` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `uploader_id`  INT UNSIGNED    NULL DEFAULT NULL COMMENT 'FK → users.id',
    `filename`     VARCHAR(300)    NOT NULL COMMENT 'Stored filename (UUID-based)',
    `original_name` VARCHAR(300)  NOT NULL COMMENT 'Original upload filename',
    `filepath`     VARCHAR(500)    NOT NULL COMMENT 'Relative path from public/ e.g. /uploads/2025/01/abc.jpg',
    `url`          VARCHAR(500)    NOT NULL COMMENT 'Public-accessible URL',
    `mime_type`    VARCHAR(100)    NOT NULL,
    `extension`    VARCHAR(10)     NOT NULL,
    `size`         INT UNSIGNED    NOT NULL DEFAULT 0 COMMENT 'File size in bytes',
    `width`        SMALLINT UNSIGNED NULL DEFAULT NULL COMMENT 'Image width px (images only)',
    `height`       SMALLINT UNSIGNED NULL DEFAULT NULL COMMENT 'Image height px (images only)',
    `alt_text`     VARCHAR(300)    NOT NULL DEFAULT '',
    `caption`      TEXT            NULL DEFAULT NULL,
    `disk`         VARCHAR(50)     NOT NULL DEFAULT 'local' COMMENT 'Storage driver: local or future S3',
    `uploaded_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_media_uploader` (`uploader_id`),
    KEY `idx_media_mime` (`mime_type`),
    KEY `idx_media_ext` (`extension`),
    KEY `idx_media_uploaded` (`uploaded_at`),

    CONSTRAINT `fk_media_uploader`
        FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Central media library for all uploaded files';

SET FOREIGN_KEY_CHECKS = 1;
