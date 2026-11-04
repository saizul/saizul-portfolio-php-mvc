# Entity Relationship Diagram — Saizul Amin Portfolio Database

**Database:** `saizul_portfolio`  
**Engine:** MySQL 8.x · InnoDB · utf8mb4_unicode_ci  
**Total Tables:** 16  
**Generated:** 2026-06-01

---

## Relationship Overview

```
users ──────────────────────────────────────────────────────────────────────┐
  │                                                                         │
  ├──< media (uploader_id)                                                  │
  │      │                                                                  │
  │      ├──< pages (og_image_id)                                           │
  │      ├──< seo_meta (og_image_id)                                        │
  │      ├──< projects (thumbnail_id)                                       │
  │      ├──< posts (featured_image_id)                                     │
  │      ├──< experience (logo_id)                                          │
  │      └──< project_gallery (media_id)                                    │
  │                                                                         │
  └──< posts (author_id) ──────────────────────────────────────────────┐   │
                                                                        │   │
categories ─────────────────────────────────────────────────────────┐  │   │
  │ (self-ref parent_id)                                             │  │   │
  ├──< projects (category_id) ──< project_tags >── tags             │  │   │
  │      └──< project_gallery                                        │  │   │
  └──< posts (category_id) ────< post_tags ──>── tags ──────────────┘  │   │
                                                                        │   │
seo_meta (page_slug, entity_type) ──── referenced by pages/posts/projects  │
settings (key/value store) ──────────────────── no FK, standalone          │
skills ──────────────────────────────────────── no FK, standalone          │
experience ─────────────────────────────────── media (logo_id)             │
contact_submissions ────────────────────────── no FK, standalone           │
migrations ─────────────────────────────────── no FK, standalone           │
```

---

## Table Definitions

### 1. `users`
Admin CMS accounts.

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | |
| `name` | VARCHAR(100) | NOT NULL | |
| `email` | VARCHAR(150) | NOT NULL, UNIQUE | |
| `password_hash` | VARCHAR(255) | NOT NULL | bcrypt cost 12 |
| `role` | ENUM | NOT NULL, DEFAULT 'admin' | `admin` \| `editor` |
| `avatar` | VARCHAR(500) | NULL | URL to profile image |
| `last_login_at` | DATETIME | NULL | Updated on login |
| `is_active` | TINYINT(1) | NOT NULL, DEFAULT 1 | Soft disable |
| `created_at` | DATETIME | NOT NULL, DEFAULT NOW | |
| `updated_at` | DATETIME | NOT NULL, ON UPDATE NOW | |

**Indexes:** `uq_users_email`, `idx_users_role`, `idx_users_active`

**Referenced by:** `media.uploader_id`, `posts.author_id`

---

### 2. `categories`
Hierarchical taxonomy shared by posts and projects. Supports one level of parent/child nesting.

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | |
| `parent_id` | INT UNSIGNED | NULL, FK → categories.id | Self-reference |
| `name` | VARCHAR(100) | NOT NULL | |
| `slug` | VARCHAR(120) | NOT NULL, UNIQUE | URL-safe |
| `description` | TEXT | NULL | |
| `type` | ENUM | NOT NULL, DEFAULT 'general' | `post` \| `project` \| `general` |
| `order` | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | Sort position |
| `created_at` | DATETIME | NOT NULL | |
| `updated_at` | DATETIME | NOT NULL | |

**Indexes:** `uq_categories_slug`, `idx_categories_parent`, `idx_categories_type`, `idx_categories_order`

**Foreign Keys:**
- `fk_categories_parent` → `categories.id` ON DELETE SET NULL

**Referenced by:** `projects.category_id`, `posts.category_id`

---

### 3. `media`
Central file library. All uploaded images, documents, and 3D models go here. Other tables reference `media.id` rather than storing raw paths.

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | |
| `uploader_id` | INT UNSIGNED | NULL, FK → users.id | |
| `filename` | VARCHAR(300) | NOT NULL | UUID-based stored name |
| `original_name` | VARCHAR(300) | NOT NULL | User's original filename |
| `filepath` | VARCHAR(500) | NOT NULL | Relative from `public/` |
| `url` | VARCHAR(500) | NOT NULL | Public URL |
| `mime_type` | VARCHAR(100) | NOT NULL | e.g. `image/webp` |
| `extension` | VARCHAR(10) | NOT NULL | e.g. `webp` |
| `size` | INT UNSIGNED | NOT NULL, DEFAULT 0 | Bytes |
| `width` | SMALLINT UNSIGNED | NULL | Images only |
| `height` | SMALLINT UNSIGNED | NULL | Images only |
| `alt_text` | VARCHAR(300) | NOT NULL, DEFAULT '' | Accessibility |
| `caption` | TEXT | NULL | Optional caption |
| `disk` | VARCHAR(50) | NOT NULL, DEFAULT 'local' | Future: s3 |
| `uploaded_at` | DATETIME | NOT NULL | |
| `updated_at` | DATETIME | NOT NULL | |

**Indexes:** `idx_media_uploader`, `idx_media_mime`, `idx_media_uploaded`

**Foreign Keys:**
- `fk_media_uploader` → `users.id` ON DELETE SET NULL

**Referenced by:** `pages.og_image_id`, `seo_meta.og_image_id`, `projects.thumbnail_id`, `posts.featured_image_id`, `experience.logo_id`, `project_gallery.media_id`

---

### 4. `pages`
One row per public page slug. Stores Page Builder JSON blocks. SEO is in `seo_meta`, not here.

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | |
| `slug` | VARCHAR(100) | NOT NULL, UNIQUE | `home`, `about`, `ai-projects`… |
| `title` | VARCHAR(200) | NOT NULL | |
| `content_json` | LONGTEXT | NULL | JSON array of builder blocks |
| `og_image_id` | INT UNSIGNED | NULL, FK → media.id | Page-level OG image |
| `is_published` | TINYINT(1) | NOT NULL, DEFAULT 1 | |
| `created_at` | DATETIME | NOT NULL | |
| `updated_at` | DATETIME | NOT NULL | |

**Indexes:** `uq_pages_slug`, `idx_pages_published`

**Foreign Keys:**
- `fk_pages_og_image` → `media.id` ON DELETE SET NULL

---

### 5. `seo_meta`
Per-slug SEO override rows. One row can exist for pages, posts, and projects independently (disambiguated by `entity_type`).

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | |
| `page_slug` | VARCHAR(200) | NOT NULL | Matches slug of target |
| `entity_type` | ENUM | NOT NULL, DEFAULT 'page' | `page` \| `post` \| `project` |
| `meta_title` | VARCHAR(200) | NULL | Falls back to site default |
| `meta_description` | VARCHAR(320) | NULL | |
| `og_title` | VARCHAR(200) | NULL | |
| `og_description` | VARCHAR(320) | NULL | |
| `og_image_id` | INT UNSIGNED | NULL, FK → media.id | |
| `og_type` | VARCHAR(50) | NOT NULL, DEFAULT 'website' | |
| `twitter_card` | ENUM | NOT NULL | `summary` \| `summary_large_image` |
| `twitter_title` | VARCHAR(200) | NULL | |
| `twitter_description` | VARCHAR(320) | NULL | |
| `canonical_url` | VARCHAR(500) | NULL | Override canonical |
| `robots_directive` | VARCHAR(100) | NOT NULL, DEFAULT 'index, follow' | |
| `schema_json` | LONGTEXT | NULL | Raw JSON-LD for `<head>` |
| `created_at` | DATETIME | NOT NULL | |
| `updated_at` | DATETIME | NOT NULL | |

**Indexes:** `uq_seo_slug_type` (page_slug + entity_type), `idx_seo_slug`

**Foreign Keys:**
- `fk_seo_og_image` → `media.id` ON DELETE SET NULL

---

### 6. `settings`
Key-value store for all site-wide configuration. Grouped for admin UI tabs.

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | |
| `key` | VARCHAR(100) | NOT NULL, UNIQUE | e.g. `site_name` |
| `value` | LONGTEXT | NULL | |
| `type` | ENUM | NOT NULL, DEFAULT 'string' | `string` \| `boolean` \| `integer` \| `json` \| `text` |
| `group` | VARCHAR(50) | NOT NULL, DEFAULT 'general' | `general` \| `seo` \| `social` \| `appearance` \| `robots` \| `mail` |
| `label` | VARCHAR(150) | NULL | Admin UI field label |
| `description` | VARCHAR(300) | NULL | Admin UI helper text |
| `is_public` | TINYINT(1) | NOT NULL, DEFAULT 0 | Safe for frontend exposure |
| `created_at` | DATETIME | NOT NULL | |
| `updated_at` | DATETIME | NOT NULL | |

**Indexes:** `uq_settings_key`, `idx_settings_group`

No foreign keys. Standalone.

---

### 7. `projects`
AI and portfolio project entries.

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | |
| `category_id` | INT UNSIGNED | NULL, FK → categories.id | |
| `thumbnail_id` | INT UNSIGNED | NULL, FK → media.id | Cover image |
| `title` | VARCHAR(200) | NOT NULL | |
| `slug` | VARCHAR(220) | NOT NULL, UNIQUE | |
| `excerpt` | VARCHAR(500) | NULL | Card teaser text |
| `description` | LONGTEXT | NULL | Full rich HTML |
| `tech_stack` | VARCHAR(500) | NULL | Comma-separated tags |
| `demo_url` | VARCHAR(500) | NULL | |
| `github_url` | VARCHAR(500) | NULL | |
| `case_study_url` | VARCHAR(500) | NULL | |
| `project_type` | ENUM | NOT NULL, DEFAULT 'portfolio' | `ai` \| `portfolio` \| `open-source` \| `client` |
| `status` | ENUM | NOT NULL, DEFAULT 'active' | `active` \| `inactive` \| `draft` |
| `is_featured` | TINYINT(1) | NOT NULL, DEFAULT 0 | Show on home |
| `order` | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | |
| `started_at` | DATE | NULL | |
| `completed_at` | DATE | NULL | NULL = ongoing |
| `created_at` | DATETIME | NOT NULL | |
| `updated_at` | DATETIME | NOT NULL | |

**Indexes:** `uq_projects_slug`, `idx_projects_category`, `idx_projects_thumbnail`, `idx_projects_type_order`, `idx_projects_status`, `idx_projects_featured`

**Foreign Keys:**
- `fk_projects_category` → `categories.id` ON DELETE SET NULL
- `fk_projects_thumbnail` → `media.id` ON DELETE SET NULL

**Referenced by:** `project_tags.project_id`, `project_gallery.project_id`

---

### 8. `posts`
Blog posts with full rich-text content.

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | |
| `author_id` | INT UNSIGNED | NULL, FK → users.id | |
| `category_id` | INT UNSIGNED | NULL, FK → categories.id | |
| `featured_image_id` | INT UNSIGNED | NULL, FK → media.id | |
| `title` | VARCHAR(300) | NOT NULL | |
| `slug` | VARCHAR(320) | NOT NULL, UNIQUE | |
| `excerpt` | TEXT | NULL | |
| `body` | LONGTEXT | NULL | Rich HTML from editor |
| `read_time` | TINYINT UNSIGNED | NOT NULL, DEFAULT 0 | Estimated minutes |
| `status` | ENUM | NOT NULL, DEFAULT 'draft' | `draft` \| `published` \| `archived` |
| `is_featured` | TINYINT(1) | NOT NULL, DEFAULT 0 | |
| `views` | INT UNSIGNED | NOT NULL, DEFAULT 0 | Incremented on visit |
| `published_at` | DATETIME | NULL | |
| `created_at` | DATETIME | NOT NULL | |
| `updated_at` | DATETIME | NOT NULL | |

**Indexes:** `uq_posts_slug`, `idx_posts_author`, `idx_posts_category`, `idx_posts_featured_image`, `idx_posts_status_published`, `idx_posts_featured`, `ft_posts_search` (FULLTEXT on title+excerpt+body)

**Foreign Keys:**
- `fk_posts_author` → `users.id` ON DELETE SET NULL
- `fk_posts_category` → `categories.id` ON DELETE SET NULL
- `fk_posts_featured_image` → `media.id` ON DELETE SET NULL

**Referenced by:** `post_tags.post_id`

---

### 9. `tags`
Flat tag list shared by posts and projects.

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | |
| `name` | VARCHAR(100) | NOT NULL | |
| `slug` | VARCHAR(120) | NOT NULL, UNIQUE | |
| `created_at` | DATETIME | NOT NULL | |

**Referenced by:** `post_tags.tag_id`, `project_tags.tag_id`

---

### 10. `post_tags` (junction)
Many-to-many between posts and tags.

| Column | Type | Constraints |
|---|---|---|
| `post_id` | INT UNSIGNED | PK (composite), FK → posts.id CASCADE |
| `tag_id` | INT UNSIGNED | PK (composite), FK → tags.id CASCADE |

---

### 11. `project_tags` (junction)
Many-to-many between projects and tags.

| Column | Type | Constraints |
|---|---|---|
| `project_id` | INT UNSIGNED | PK (composite), FK → projects.id CASCADE |
| `tag_id` | INT UNSIGNED | PK (composite), FK → tags.id CASCADE |

---

### 12. `project_gallery` (junction)
Ordered extra images per project.

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | |
| `project_id` | INT UNSIGNED | NOT NULL, FK → projects.id CASCADE | |
| `media_id` | INT UNSIGNED | NOT NULL, FK → media.id CASCADE | |
| `order` | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | |
| `caption` | VARCHAR(300) | NULL | |

---

### 13. `skills`
Technical skills with proficiency ratings. No FK dependencies.

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | |
| `name` | VARCHAR(100) | NOT NULL | |
| `category` | VARCHAR(100) | NOT NULL | `Languages` \| `Frontend` \| `AI / ML` \| `Databases` \| `DevOps` |
| `proficiency` | TINYINT UNSIGNED | NOT NULL, DEFAULT 0 | 0–100 |
| `icon` | VARCHAR(200) | NULL | Icon name or path |
| `color` | VARCHAR(7) | NULL | Hex color for 3D bubble |
| `order` | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | |

**Indexes:** `idx_skills_category_order`

---

### 14. `experience`
Work, freelance, and education timeline entries.

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | |
| `company` | VARCHAR(200) | NOT NULL | |
| `role` | VARCHAR(200) | NOT NULL | |
| `description` | TEXT | NULL | |
| `location` | VARCHAR(150) | NULL | |
| `logo_id` | INT UNSIGNED | NULL, FK → media.id | Company logo |
| `start_date` | DATE | NOT NULL | |
| `end_date` | DATE | NULL | NULL = current |
| `is_current` | TINYINT(1) | NOT NULL, DEFAULT 0 | |
| `type` | ENUM | NOT NULL, DEFAULT 'work' | `work` \| `education` \| `freelance` |
| `order` | SMALLINT UNSIGNED | NOT NULL, DEFAULT 0 | |
| `created_at` | DATETIME | NOT NULL | |
| `updated_at` | DATETIME | NOT NULL | |

**Foreign Keys:**
- `fk_experience_logo` → `media.id` ON DELETE SET NULL

---

### 15. `contact_submissions`
Public contact form entries. No FK dependencies.

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | |
| `name` | VARCHAR(150) | NOT NULL | |
| `email` | VARCHAR(150) | NOT NULL | |
| `subject` | VARCHAR(300) | NULL | |
| `message` | TEXT | NOT NULL | |
| `ip` | VARCHAR(45) | NULL | IPv4 or IPv6 |
| `user_agent` | VARCHAR(500) | NULL | |
| `is_read` | TINYINT(1) | NOT NULL, DEFAULT 0 | |
| `is_replied` | TINYINT(1) | NOT NULL, DEFAULT 0 | |
| `replied_at` | DATETIME | NULL | |
| `created_at` | DATETIME | NOT NULL | |

**Indexes:** `idx_contact_read`, `idx_contact_email`, `idx_contact_created`

---

### 16. `migrations`
Tracks which migration files have been executed.

| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | |
| `filename` | VARCHAR(200) | NOT NULL, UNIQUE | e.g. `001_create_users_table.sql` |
| `batch` | SMALLINT UNSIGNED | NOT NULL, DEFAULT 1 | Group of migrations run together |
| `run_at` | DATETIME | NOT NULL | |

---

## Foreign Key Map

```
media.uploader_id           → users.id            (SET NULL)
pages.og_image_id           → media.id            (SET NULL)
seo_meta.og_image_id        → media.id            (SET NULL)
projects.category_id        → categories.id       (SET NULL)
projects.thumbnail_id       → media.id            (SET NULL)
posts.author_id             → users.id            (SET NULL)
posts.category_id           → categories.id       (SET NULL)
posts.featured_image_id     → media.id            (SET NULL)
post_tags.post_id           → posts.id            (CASCADE)
post_tags.tag_id            → tags.id             (CASCADE)
project_tags.project_id     → projects.id         (CASCADE)
project_tags.tag_id         → tags.id             (CASCADE)
project_gallery.project_id  → projects.id         (CASCADE)
project_gallery.media_id    → media.id            (CASCADE)
experience.logo_id          → media.id            (SET NULL)
categories.parent_id        → categories.id       (SET NULL)
```

**Rule:** All ownership FKs use `ON DELETE SET NULL` — deleting a user or media file never cascades to content. Junction tables use `ON DELETE CASCADE` so orphan rows are cleaned up automatically.

---

## Index Strategy

| Table | Index Type | Columns | Purpose |
|---|---|---|---|
| `users` | UNIQUE | `email` | Login lookup |
| `categories` | UNIQUE | `slug` | URL routing |
| `media` | KEY | `mime_type` | Filter by file type |
| `media` | KEY | `uploaded_at` | Sort by date |
| `pages` | UNIQUE | `slug` | CMS lookup |
| `seo_meta` | UNIQUE | `page_slug, entity_type` | One SEO row per slug+type |
| `settings` | UNIQUE | `key` | Fast get/set |
| `settings` | KEY | `group` | Load all settings for a tab |
| `projects` | UNIQUE | `slug` | URL routing |
| `projects` | KEY | `project_type, order` | Filtered, sorted lists |
| `projects` | KEY | `status` | Active-only queries |
| `posts` | UNIQUE | `slug` | URL routing |
| `posts` | KEY | `status, published_at` | Published feed |
| `posts` | FULLTEXT | `title, excerpt, body` | Site search |
| `tags` | UNIQUE | `slug` | Tag routing |
| `skills` | KEY | `category, order` | Grouped, sorted listing |
| `experience` | KEY | `type, order` | Filtered timeline |
| `contact_submissions` | KEY | `is_read` | Unread count |

---

## Deployment Checklist

```bash
# 1. Create database on cPanel MySQL
CREATE DATABASE saizul_portfolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'saizul_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON saizul_portfolio.* TO 'saizul_user'@'localhost';
FLUSH PRIVILEGES;

# 2. Import schema
mysql -u saizul_user -p saizul_portfolio < database/schema.sql

# 3. Import seed data
mysql -u saizul_user -p saizul_portfolio < database/seeds/seed.sql

# 4. Verify tables
mysql -u saizul_user -p saizul_portfolio -e "SHOW TABLES;"

# 5. Update .env
DB_HOST=127.0.0.1
DB_DATABASE=saizul_portfolio
DB_USERNAME=saizul_user
DB_PASSWORD=your_secure_password
```

**Default admin login after seed:**
- Email: `saizulamin@gmail.com`
- Password: `Admin@2025`
- **Change immediately at `/admin/settings`**
