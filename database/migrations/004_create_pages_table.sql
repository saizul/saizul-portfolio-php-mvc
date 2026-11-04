-- ============================================================
-- Migration 004 — Create pages table
-- CMS Page Builder content. One row per public page slug.
-- content_json holds the block array from the drag-drop builder.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `pages` (
    `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `slug`             VARCHAR(100)    NOT NULL COMMENT 'URL slug: home, about, ai-projects …',
    `title`            VARCHAR(200)    NOT NULL,
    `content_json`     LONGTEXT        NULL DEFAULT NULL
                                       COMMENT 'JSON array of Page Builder blocks',
    `og_image_id`      INT UNSIGNED    NULL DEFAULT NULL COMMENT 'FK → media.id',
    `is_published`     TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_pages_slug` (`slug`),
    KEY `idx_pages_published` (`is_published`),

    CONSTRAINT `fk_pages_og_image`
        FOREIGN KEY (`og_image_id`) REFERENCES `media` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='CMS Page Builder content per public page';

SET FOREIGN_KEY_CHECKS = 1;
