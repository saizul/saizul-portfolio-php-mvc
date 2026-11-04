-- ============================================================
-- Migration 008 — Create posts table
-- Blog posts. author_id FK → users, category_id FK → categories,
-- featured_image_id FK → media.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `posts` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `author_id`         INT UNSIGNED    NULL DEFAULT NULL COMMENT 'FK → users.id',
    `category_id`       INT UNSIGNED    NULL DEFAULT NULL COMMENT 'FK → categories.id',
    `featured_image_id` INT UNSIGNED    NULL DEFAULT NULL COMMENT 'FK → media.id',
    `title`             VARCHAR(300)    NOT NULL,
    `slug`              VARCHAR(320)    NOT NULL,
    `excerpt`           TEXT            NULL DEFAULT NULL,
    `body`              LONGTEXT        NULL DEFAULT NULL COMMENT 'Rich HTML from editor',
    `read_time`         TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Estimated minutes',
    `status`            ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `is_featured`       TINYINT(1)      NOT NULL DEFAULT 0,
    `views`             INT UNSIGNED    NOT NULL DEFAULT 0,
    `published_at`      DATETIME        NULL DEFAULT NULL,
    `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_posts_slug` (`slug`),
    KEY `idx_posts_author` (`author_id`),
    KEY `idx_posts_category` (`category_id`),
    KEY `idx_posts_featured_image` (`featured_image_id`),
    KEY `idx_posts_status_published` (`status`, `published_at`),
    KEY `idx_posts_featured` (`is_featured`),
    FULLTEXT KEY `ft_posts_search` (`title`, `excerpt`, `body`),

    CONSTRAINT `fk_posts_author`
        FOREIGN KEY (`author_id`) REFERENCES `users` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT `fk_posts_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT `fk_posts_featured_image`
        FOREIGN KEY (`featured_image_id`) REFERENCES `media` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Blog posts with rich text content';

SET FOREIGN_KEY_CHECKS = 1;
