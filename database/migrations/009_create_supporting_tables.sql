-- ============================================================
-- Migration 009 — Supporting tables
-- skills, experience, contact_submissions, post_tags,
-- project_gallery (media junction), migrations tracker
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------
-- tags — shared flat tag list
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tags` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100)    NOT NULL,
    `slug`       VARCHAR(120)    NOT NULL,
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_tags_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Flat tag list shared by posts and projects';

-- ----------------------------------------------------------
-- post_tags — many-to-many: posts ↔ tags
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `post_tags` (
    `post_id` INT UNSIGNED NOT NULL,
    `tag_id`  INT UNSIGNED NOT NULL,

    PRIMARY KEY (`post_id`, `tag_id`),
    KEY `idx_post_tags_tag` (`tag_id`),

    CONSTRAINT `fk_post_tags_post`
        FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_post_tags_tag`
        FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Many-to-many: posts ↔ tags';

-- ----------------------------------------------------------
-- project_tags — many-to-many: projects ↔ tags
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `project_tags` (
    `project_id` INT UNSIGNED NOT NULL,
    `tag_id`     INT UNSIGNED NOT NULL,

    PRIMARY KEY (`project_id`, `tag_id`),
    KEY `idx_project_tags_tag` (`tag_id`),

    CONSTRAINT `fk_project_tags_project`
        FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_project_tags_tag`
        FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Many-to-many: projects ↔ tags';

-- ----------------------------------------------------------
-- project_gallery — extra images per project
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `project_gallery` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `project_id` INT UNSIGNED    NOT NULL,
    `media_id`   INT UNSIGNED    NOT NULL,
    `order`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `caption`    VARCHAR(300)    NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    KEY `idx_gallery_project` (`project_id`),
    KEY `idx_gallery_media` (`media_id`),

    CONSTRAINT `fk_gallery_project`
        FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_gallery_media`
        FOREIGN KEY (`media_id`) REFERENCES `media` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Additional gallery images per project';

-- ----------------------------------------------------------
-- skills
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `skills` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100)    NOT NULL,
    `category`    VARCHAR(100)    NOT NULL COMMENT 'e.g. Languages, Frontend, AI/ML, DevOps',
    `proficiency` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0–100 percentage',
    `icon`        VARCHAR(200)    NULL DEFAULT NULL COMMENT 'Icon name or SVG path',
    `color`       VARCHAR(7)      NULL DEFAULT NULL COMMENT 'Hex color for 3D bubble',
    `order`       SMALLINT UNSIGNED NOT NULL DEFAULT 0,

    PRIMARY KEY (`id`),
    KEY `idx_skills_category_order` (`category`, `order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Skills with proficiency ratings and categories';

-- ----------------------------------------------------------
-- experience
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `experience` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `company`      VARCHAR(200)    NOT NULL,
    `role`         VARCHAR(200)    NOT NULL,
    `description`  TEXT            NULL DEFAULT NULL,
    `location`     VARCHAR(150)    NULL DEFAULT NULL,
    `logo_id`      INT UNSIGNED    NULL DEFAULT NULL COMMENT 'FK → media.id',
    `start_date`   DATE            NOT NULL,
    `end_date`     DATE            NULL DEFAULT NULL,
    `is_current`   TINYINT(1)      NOT NULL DEFAULT 0,
    `type`         ENUM('work','education','freelance') NOT NULL DEFAULT 'work',
    `order`        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_experience_type_order` (`type`, `order`),
    KEY `idx_experience_logo` (`logo_id`),

    CONSTRAINT `fk_experience_logo`
        FOREIGN KEY (`logo_id`) REFERENCES `media` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Work and education timeline';

-- ----------------------------------------------------------
-- contact_submissions
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_submissions` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(150)    NOT NULL,
    `email`      VARCHAR(150)    NOT NULL,
    `subject`    VARCHAR(300)    NULL DEFAULT NULL,
    `message`    TEXT            NOT NULL,
    `ip`         VARCHAR(45)     NULL DEFAULT NULL COMMENT 'IPv4 or IPv6',
    `user_agent` VARCHAR(500)    NULL DEFAULT NULL,
    `is_read`    TINYINT(1)      NOT NULL DEFAULT 0,
    `is_replied` TINYINT(1)      NOT NULL DEFAULT 0,
    `replied_at` DATETIME        NULL DEFAULT NULL,
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_contact_read` (`is_read`),
    KEY `idx_contact_email` (`email`),
    KEY `idx_contact_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Contact form submissions from public site';

-- ----------------------------------------------------------
-- migrations — tracks which migration files have run
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `migrations` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `filename`   VARCHAR(200)    NOT NULL COMMENT 'e.g. 001_create_users_table.sql',
    `batch`      SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    `run_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_migrations_filename` (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Migration run log';

SET FOREIGN_KEY_CHECKS = 1;
