-- ============================================================
-- Migration 005 — Create seo_meta table
-- Per-page SEO overrides: title, description, OG, Twitter,
-- canonical URL, robots directive, and Schema.org JSON-LD.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `seo_meta` (
    `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `page_slug`        VARCHAR(200)    NOT NULL COMMENT 'Matches pages.slug or post/project slug',
    `entity_type`      ENUM('page','post','project') NOT NULL DEFAULT 'page'
                                       COMMENT 'Which content type this SEO row belongs to',

    -- Basic meta
    `meta_title`       VARCHAR(200)    NULL DEFAULT NULL,
    `meta_description` VARCHAR(320)    NULL DEFAULT NULL,

    -- Open Graph
    `og_title`         VARCHAR(200)    NULL DEFAULT NULL,
    `og_description`   VARCHAR(320)    NULL DEFAULT NULL,
    `og_image_id`      INT UNSIGNED    NULL DEFAULT NULL COMMENT 'FK → media.id',
    `og_type`          VARCHAR(50)     NOT NULL DEFAULT 'website',

    -- Twitter Card
    `twitter_card`     ENUM('summary','summary_large_image') NOT NULL DEFAULT 'summary_large_image',
    `twitter_title`    VARCHAR(200)    NULL DEFAULT NULL,
    `twitter_description` VARCHAR(320) NULL DEFAULT NULL,

    -- Technical SEO
    `canonical_url`    VARCHAR(500)    NULL DEFAULT NULL,
    `robots_directive` VARCHAR(100)    NOT NULL DEFAULT 'index, follow',

    -- Schema.org JSON-LD
    `schema_json`      LONGTEXT        NULL DEFAULT NULL
                                       COMMENT 'Raw JSON-LD to inject in <head>',

    `created_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_seo_slug_type` (`page_slug`, `entity_type`),
    KEY `idx_seo_slug` (`page_slug`),
    KEY `idx_seo_entity_type` (`entity_type`),

    CONSTRAINT `fk_seo_og_image`
        FOREIGN KEY (`og_image_id`) REFERENCES `media` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Per-page/post/project SEO metadata and Schema.org output';

SET FOREIGN_KEY_CHECKS = 1;
