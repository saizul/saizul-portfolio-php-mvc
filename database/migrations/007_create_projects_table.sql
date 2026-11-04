-- ============================================================
-- Migration 007 — Create projects table
-- AI projects and portfolio work items.
-- category_id FK → categories (type = 'project')
-- thumbnail_id FK → media
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `projects` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `category_id`   INT UNSIGNED    NULL DEFAULT NULL COMMENT 'FK → categories.id',
    `thumbnail_id`  INT UNSIGNED    NULL DEFAULT NULL COMMENT 'FK → media.id',
    `title`         VARCHAR(200)    NOT NULL,
    `slug`          VARCHAR(220)    NOT NULL,
    `excerpt`       VARCHAR(500)    NULL DEFAULT NULL COMMENT 'Short teaser for cards',
    `description`   LONGTEXT        NULL DEFAULT NULL COMMENT 'Full rich-text description',
    `tech_stack`    VARCHAR(500)    NULL DEFAULT NULL COMMENT 'Comma-separated tags: Python,React,…',
    `demo_url`      VARCHAR(500)    NULL DEFAULT NULL,
    `github_url`    VARCHAR(500)    NULL DEFAULT NULL,
    `case_study_url` VARCHAR(500)   NULL DEFAULT NULL,
    `project_type`  ENUM('ai','portfolio','open-source','client') NOT NULL DEFAULT 'portfolio',
    `status`        ENUM('active','inactive','draft') NOT NULL DEFAULT 'active',
    `is_featured`   TINYINT(1)      NOT NULL DEFAULT 0,
    `order`         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `started_at`    DATE            NULL DEFAULT NULL,
    `completed_at`  DATE            NULL DEFAULT NULL,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_projects_slug` (`slug`),
    KEY `idx_projects_category` (`category_id`),
    KEY `idx_projects_thumbnail` (`thumbnail_id`),
    KEY `idx_projects_type_order` (`project_type`, `order`),
    KEY `idx_projects_status` (`status`),
    KEY `idx_projects_featured` (`is_featured`),

    CONSTRAINT `fk_projects_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT `fk_projects_thumbnail`
        FOREIGN KEY (`thumbnail_id`) REFERENCES `media` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='AI and portfolio project entries';

SET FOREIGN_KEY_CHECKS = 1;
