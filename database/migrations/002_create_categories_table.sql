-- ============================================================
-- Migration 002 — Create categories table
-- Shared taxonomy for posts and projects.
-- Supports self-referential parent/child hierarchy.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `categories` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `parent_id`   INT UNSIGNED    NULL DEFAULT NULL COMMENT 'Self-reference for subcategories',
    `name`        VARCHAR(100)    NOT NULL,
    `slug`        VARCHAR(120)    NOT NULL,
    `description` TEXT            NULL DEFAULT NULL,
    `type`        ENUM('post','project','general') NOT NULL DEFAULT 'general'
                                  COMMENT 'Which content type this category belongs to',
    `order`       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_categories_slug` (`slug`),
    KEY `idx_categories_parent` (`parent_id`),
    KEY `idx_categories_type` (`type`),
    KEY `idx_categories_order` (`order`),

    CONSTRAINT `fk_categories_parent`
        FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Hierarchical taxonomy shared by posts and projects';

SET FOREIGN_KEY_CHECKS = 1;
