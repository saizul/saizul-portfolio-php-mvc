-- ═══════════════════════════════════════════════════════════════
-- Saizul Amin Portfolio — Production Schema
-- MySQL 8.x / MariaDB 10.6+
-- Run once on fresh database: mysql -u user -p dbname < schema.sql
-- ═══════════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── Users (admin accounts) ────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(120)     NOT NULL,
  `email`         VARCHAR(180)     NOT NULL,
  `password_hash` VARCHAR(255)     NOT NULL,
  `role`          ENUM('admin','editor') NOT NULL DEFAULT 'editor',
  `last_login`    DATETIME         NULL,
  `created_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Password resets ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`      VARCHAR(180) NOT NULL,
  `token`      VARCHAR(255) NOT NULL,
  `expires_at` DATETIME     NOT NULL,
  `used`       TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pr_email`  (`email`),
  KEY `idx_pr_token`  (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Pages (CMS page builder content) ─────────────────────────
CREATE TABLE IF NOT EXISTS `pages` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`         VARCHAR(120) NOT NULL,
  `title`        VARCHAR(255) NOT NULL,
  `content_json` LONGTEXT     NULL,
  `meta_title`   VARCHAR(120) NULL,
  `meta_desc`    VARCHAR(320) NULL,
  `og_image`     VARCHAR(512) NULL,
  `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pages_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Posts (blog) ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `posts` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`          VARCHAR(255) NOT NULL,
  `slug`           VARCHAR(255) NOT NULL,
  `excerpt`        TEXT         NULL,
  `body`           LONGTEXT     NULL,
  `featured_image` VARCHAR(512) NULL,
  `status`         ENUM('draft','published') NOT NULL DEFAULT 'draft',
  `published_at`   DATETIME     NULL,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_posts_slug`    (`slug`),
  KEY `idx_posts_status`        (`status`),
  KEY `idx_posts_published_at`  (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Projects (AI + Portfolio) ─────────────────────────────────
CREATE TABLE IF NOT EXISTS `projects` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(255) NOT NULL,
  `slug`        VARCHAR(255) NOT NULL,
  `description` TEXT         NULL,
  `tech_stack`  VARCHAR(512) NULL,
  `thumbnail`   VARCHAR(512) NULL,
  `demo_url`    VARCHAR(512) NULL,
  `github_url`  VARCHAR(512) NULL,
  `category`    ENUM('ai','portfolio') NOT NULL DEFAULT 'portfolio',
  `featured`    TINYINT(1)   NOT NULL DEFAULT 0,
  `order_col`   INT UNSIGNED NOT NULL DEFAULT 0,
  `status`      ENUM('draft','published') NOT NULL DEFAULT 'published',
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_projects_slug`    (`slug`),
  KEY `idx_projects_category`      (`category`),
  KEY `idx_projects_status_order`  (`status`,`order_col`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Skills ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `skills` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100) NOT NULL,
  `category`    VARCHAR(80)  NOT NULL DEFAULT 'General',
  `proficiency` TINYINT UNSIGNED NOT NULL DEFAULT 80,
  `icon`        VARCHAR(255) NULL,
  `order_col`   INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_skills_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Experience ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `experience` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company`     VARCHAR(200) NOT NULL,
  `role`        VARCHAR(200) NOT NULL,
  `description` TEXT         NULL,
  `start_date`  DATE         NOT NULL,
  `end_date`    DATE         NULL,
  `is_current`  TINYINT(1)   NOT NULL DEFAULT 0,
  `type`        ENUM('work','education') NOT NULL DEFAULT 'work',
  `order_col`   INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_exp_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Media ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `media` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename`    VARCHAR(255) NOT NULL,
  `filepath`    VARCHAR(512) NOT NULL,
  `mime_type`   VARCHAR(80)  NOT NULL,
  `alt_text`    VARCHAR(255) NULL,
  `size`        INT UNSIGNED NOT NULL DEFAULT 0,
  `uploaded_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_media_mime` (`mime_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── SEO meta ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `seo_meta` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_slug`        VARCHAR(120) NOT NULL,
  `meta_title`       VARCHAR(120) NULL,
  `meta_description` VARCHAR(320) NULL,
  `og_title`         VARCHAR(180) NULL,
  `og_description`   VARCHAR(320) NULL,
  `og_image`         VARCHAR(512) NULL,
  `schema_json`      TEXT         NULL,
  `canonical_url`    VARCHAR(512) NULL,
  `robots_directive` VARCHAR(60)  NOT NULL DEFAULT 'index, follow',
  `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_seo_slug` (`page_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Settings (key-value store) ────────────────────────────────
CREATE TABLE IF NOT EXISTS `settings` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key_name`   VARCHAR(120) NOT NULL,
  `value`      TEXT         NULL,
  `group_name` VARCHAR(60)  NOT NULL DEFAULT 'general',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Contact submissions ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS `contact_submissions` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(150) NOT NULL,
  `email`      VARCHAR(150) NOT NULL,
  `subject`    VARCHAR(300) NULL,
  `message`    TEXT         NOT NULL,
  `ip`         VARCHAR(45)  NULL COMMENT 'IPv4 or IPv6',
  `user_agent` VARCHAR(500) NULL,
  `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
  `is_replied` TINYINT(1)   NOT NULL DEFAULT 0,
  `replied_at` DATETIME     NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contact_read`    (`is_read`),
  KEY `idx_contact_email`   (`email`),
  KEY `idx_contact_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ══════════════════════════════════════════════════════
-- EXPANSION TABLES (added by module build phase 2)
-- ══════════════════════════════════════════════════════

-- Portfolio categories
CREATE TABLE IF NOT EXISTS `portfolio_categories` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(120) NOT NULL,
  `slug`        VARCHAR(120) NOT NULL,
  `description` VARCHAR(512) NULL,
  `color`       VARCHAR(7)   NOT NULL DEFAULT '#c0392b',
  `order_col`   INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_portcat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Portfolio images (gallery, multiple per project)
CREATE TABLE IF NOT EXISTS `portfolio_images` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` INT UNSIGNED NOT NULL,
  `filepath`   VARCHAR(512) NOT NULL,
  `alt_text`   VARCHAR(255) NULL,
  `order_col`  INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_portimg_project` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Extend projects table (portfolio full fields)
ALTER TABLE `projects`
  ADD COLUMN IF NOT EXISTS `client_name`        VARCHAR(255) NULL AFTER `title`,
  ADD COLUMN IF NOT EXISTS `country`            VARCHAR(120) NULL AFTER `client_name`,
  ADD COLUMN IF NOT EXISTS `project_type`       VARCHAR(120) NULL AFTER `country`,
  ADD COLUMN IF NOT EXISTS `case_study`         LONGTEXT     NULL AFTER `description`,
  ADD COLUMN IF NOT EXISTS `results`            TEXT         NULL AFTER `case_study`,
  ADD COLUMN IF NOT EXISTS `live_url`           VARCHAR(512) NULL AFTER `results`,
  ADD COLUMN IF NOT EXISTS `video_url`          VARCHAR(512) NULL AFTER `live_url`,
  ADD COLUMN IF NOT EXISTS `portfolio_cat_id`   INT UNSIGNED NULL AFTER `category`,
  ADD COLUMN IF NOT EXISTS `meta_title`         VARCHAR(255) NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `meta_description`   TEXT         NULL AFTER `meta_title`;

-- AI Projects (separate from portfolio projects)
CREATE TABLE IF NOT EXISTS `ai_projects` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`             VARCHAR(255) NOT NULL,
  `slug`              VARCHAR(255) NOT NULL,
  `problem`           TEXT         NULL,
  `solution`          TEXT         NULL,
  `workflow_diagram`  VARCHAR(512) NULL,
  `tech_stack`        TEXT         NULL,
  `architecture_notes`LONGTEXT     NULL,
  `results`           TEXT         NULL,
  `video_url`         VARCHAR(512) NULL,
  `images`            TEXT         NULL COMMENT 'JSON array of image paths',
  `status`            ENUM('draft','published') NOT NULL DEFAULT 'published',
  `order_col`         INT UNSIGNED NOT NULL DEFAULT 0,
  `meta_title`        VARCHAR(255) NULL,
  `meta_description`  TEXT         NULL,
  `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_aiproj_slug` (`slug`),
  KEY `idx_aiproj_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics events
CREATE TABLE IF NOT EXISTS `analytics_events` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id`   VARCHAR(64)     NOT NULL,
  `page_path`    VARCHAR(512)    NOT NULL,
  `referrer`     VARCHAR(512)    NULL,
  `user_agent`   VARCHAR(512)    NULL,
  `device_type`  ENUM('desktop','mobile','tablet') NOT NULL DEFAULT 'desktop',
  `browser`      VARCHAR(80)     NULL,
  `os`           VARCHAR(80)     NULL,
  `country_code` VARCHAR(4)      NULL,
  `ip_hash`      VARCHAR(64)     NULL COMMENT 'SHA256 of IP for privacy',
  `duration_sec` SMALLINT UNSIGNED NULL,
  `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_analytics_path`  (`page_path`(100)),
  KEY `idx_analytics_date`  (`created_at`),
  KEY `idx_analytics_sess`  (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menu items
CREATE TABLE IF NOT EXISTS `menu_items` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `label`     VARCHAR(120) NOT NULL,
  `url`       VARCHAR(512) NOT NULL,
  `target`    VARCHAR(20)  NOT NULL DEFAULT '_self',
  `parent_id` INT UNSIGNED NULL,
  `order_col` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_menu_parent` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Homepage sections (dynamic)
CREATE TABLE IF NOT EXISTS `homepage_sections` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_key`  VARCHAR(80)  NOT NULL,
  `title`        VARCHAR(255) NULL,
  `subtitle`     TEXT         NULL,
  `content_json` LONGTEXT     NULL COMMENT 'Block content JSON',
  `order_col`    INT UNSIGNED NOT NULL DEFAULT 0,
  `is_enabled`   TINYINT(1)   NOT NULL DEFAULT 1,
  `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_section_key` (`section_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Footer blocks
CREATE TABLE IF NOT EXISTS `footer_blocks` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `block_key` VARCHAR(80)  NOT NULL,
  `title`     VARCHAR(120) NULL,
  `content`   TEXT         NULL,
  `order_col` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_footer_key` (`block_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Post categories
CREATE TABLE IF NOT EXISTS `post_categories` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(120) NOT NULL,
  `slug`        VARCHAR(120) NOT NULL,
  `description` VARCHAR(512) NULL,
  `color`       VARCHAR(7)   NOT NULL DEFAULT '#6ee7b7',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_postcat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Post tags
CREATE TABLE IF NOT EXISTS `post_tags` (
  `id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80)  NOT NULL,
  `slug` VARCHAR(80)  NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_posttag_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Post ↔ tag pivot
CREATE TABLE IF NOT EXISTS `post_tag_pivot` (
  `post_id` INT UNSIGNED NOT NULL,
  `tag_id`  INT UNSIGNED NOT NULL,
  PRIMARY KEY (`post_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Extend posts
ALTER TABLE `posts`
  ADD COLUMN IF NOT EXISTS `category_id`      INT UNSIGNED NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `reading_time_min` TINYINT UNSIGNED NULL AFTER `category_id`,
  ADD COLUMN IF NOT EXISTS `meta_title`       VARCHAR(255) NULL AFTER `reading_time_min`,
  ADD COLUMN IF NOT EXISTS `meta_description` TEXT         NULL AFTER `meta_title`,
  ADD COLUMN IF NOT EXISTS `og_image`         VARCHAR(512) NULL AFTER `meta_description`;

-- Theme settings
CREATE TABLE IF NOT EXISTS `theme_settings` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key_name`   VARCHAR(80)  NOT NULL,
  `value`      TEXT         NULL,
  `group_name` VARCHAR(40)  NOT NULL DEFAULT 'general',
  `label`      VARCHAR(120) NULL,
  `type`       ENUM('color','font','text','select','toggle','number') NOT NULL DEFAULT 'text',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_theme_key` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Media folders
ALTER TABLE `media`
  ADD COLUMN IF NOT EXISTS `folder`      ENUM('portfolio','projects','blog','profile','general') NOT NULL DEFAULT 'general' AFTER `alt_text`,
  ADD COLUMN IF NOT EXISTS `webp_path`   VARCHAR(512) NULL AFTER `folder`,
  ADD COLUMN IF NOT EXISTS `width`       SMALLINT UNSIGNED NULL AFTER `webp_path`,
  ADD COLUMN IF NOT EXISTS `height`      SMALLINT UNSIGNED NULL AFTER `width`,
  ADD COLUMN IF NOT EXISTS `title`       VARCHAR(255) NULL AFTER `height`,
  ADD COLUMN IF NOT EXISTS `description` TEXT         NULL AFTER `title`;

