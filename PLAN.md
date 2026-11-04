# PLAN.md — Saizul Amin Personal Brand Platform

## Implementation Plan

This document defines the complete phase-by-phase build plan. No code is generated here — this is the authoritative implementation blueprint.

---

## Phase 0: Project Bootstrap & Environment Setup

**Goal:** Establish the project skeleton, configuration, and local/cPanel environment.

### Tasks
- [ ] 0.1 Create full directory structure
- [ ] 0.2 Create `.env.example` with all required keys (DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL, APP_ENV, APP_KEY)
- [ ] 0.3 Create `.htaccess` at root to redirect all traffic to `public/`
- [ ] 0.4 Create `public/.htaccess` with mod_rewrite front-controller rules
- [ ] 0.5 Create `public/index.php` as the application entry point
- [ ] 0.6 Create `config/app.php`, `config/database.php`, `config/routes.php` stubs
- [ ] 0.7 Create `app/Helpers/functions.php` with global helpers: `e()`, `url()`, `asset()`, `redirect()`, `dd()`
- [ ] 0.8 Set up `storage/cache/`, `storage/logs/`, `public/uploads/` directories with `.gitkeep`

### Deliverables
- Bootable project skeleton
- All directories in place
- Environment config pattern established

---

## Phase 1: Core MVC Engine

**Goal:** Build the custom PHP MVC framework that powers all controllers, models, and views.

### 1.1 Database Layer (`app/Core/Database.php`)
- PDO singleton factory
- Connection via `.env` credentials
- Error mode: `PDO::ERRMODE_EXCEPTION`
- Charset: `utf8mb4`

### 1.2 Base Model (`app/Core/Model.php`)
- PDO instance injection
- Methods: `find($id)`, `findAll()`, `where($col, $val)`, `create($data)`, `update($id, $data)`, `delete($id)`
- All queries use prepared statements

### 1.3 Router (`app/Core/Router.php`)
- Register routes: `get($uri, $handler)`, `post($uri, $handler)`
- Match current URI against registered routes
- Support named route parameters: `/blog/{slug}`
- Dispatch to `ControllerClass@method`
- Return 404 view on no match

### 1.4 Base Controller (`app/Core/Controller.php`)
- `render($view, $data)` — load view with data extracted
- `redirect($url)` — header redirect
- `json($data)` — JSON response helper
- `isPost()` — method check helper
- Admin controllers extend with `Auth::check()` guard

### 1.5 View Renderer (`app/Core/View.php`)
- Resolve view path from dot notation (`pages.home` → `app/Views/pages/home.php`)
- Pass `$data` array via `extract()`
- Wrap in layout file (`layouts/main.php` or `layouts/admin.php`)

### 1.6 Auth System (`app/Core/Auth.php`)
- `login($email, $password)` — verify against DB, start session
- `logout()` — destroy session
- `check()` — return bool, redirect to `/admin/login` if false
- `user()` — return current user array from session
- `generateCsrfToken()` / `verifyCsrfToken($token)` — CSRF protection

### 1.7 Cache (`app/Core/Cache.php`)
- File-based cache in `storage/cache/`
- `get($key)`, `set($key, $value, $ttl)`, `forget($key)`, `flush()`
- Cache key hashed to filename
- TTL checked on read

### 1.8 Upload Handler (`app/Core/Upload.php`)
- Validate MIME type against whitelist
- Validate file size (max 10MB)
- Generate unique filename (UUID or timestamp + hash)
- Move to `public/uploads/`
- Return relative URL

---

## Phase 2: Database Schema

**Goal:** Define and create all MySQL tables.

### 2.1 Write `database/schema.sql`
Tables (full column definitions with types, constraints, indexes):

```
users
pages
posts
projects
skills
experience
media
seo_meta
settings
contact_submissions
```

### 2.2 Write `database/seeds/seed.sql`
- 1 admin user (hashed password)
- Sample blog posts (3)
- Sample projects (4)
- Sample skills (10)
- Sample experience entries (4)
- Default site settings rows

### Key Design Decisions
- `posts.body` — LONGTEXT (stores rich HTML)
- `pages.content_json` — JSON column (Page Builder block data)
- `seo_meta.schema_json` — JSON column (Schema.org output)
- `settings` — key/value with `group` column for grouping in UI
- All tables use `utf8mb4_unicode_ci` collation

---

## Phase 3: Public Models

**Goal:** One model class per domain entity, all extending Base Model.

| Model | Key Methods |
|---|---|
| `Post.php` | `published()`, `findBySlug($slug)`, `paginate($page, $perPage)` |
| `Project.php` | `byCategory($cat)`, `ordered()` |
| `Skill.php` | `byCategory()`, `ordered()` |
| `Experience.php` | `ordered()`, `workOnly()`, `educationOnly()` |
| `Media.php` | `recent($limit)`, `byMime($type)` |
| `Page.php` | `findBySlug($slug)`, `getBlocks($slug)` |
| `SEOMeta.php` | `forPage($slug)` |
| `User.php` | `findByEmail($email)` |
| `Setting.php` | `get($key)`, `set($key, $value)`, `group($name)` |

---

## Phase 4: Public Pages — Controllers & Views

**Goal:** All 8 public-facing pages, fully server-rendered, SEO-complete.

### 4.1 Layout: `app/Views/layouts/main.php`
- `<head>` with dynamic `<title>`, meta, OG, Twitter Card, Schema JSON-LD (via SEO.php)
- Skip-to-content link
- Navigation: logo + links (mobile hamburger)
- Footer: social links, copyright
- Lenis + GSAP core JS included
- Page-specific JS loaded via `$pageScript` variable (dynamic import)

### 4.2 Pages

#### Home (`/`)
- Controller: `HomeController@index`
- Data: featured projects (3), latest blog posts (3), skills summary
- View sections: Hero (3D canvas), Featured Work, About Teaser, Blog Preview, CTA
- 3D: particle field / floating geometry (home.js, lazy)
- SEO: Homepage schema (WebSite + Person)

#### About (`/about`)
- Controller: `AboutController@index`
- Data: page content blocks from Page Builder, experience entries
- View sections: Hero, Story, Timeline, Values/Approach, CTA
- 3D: orbital rings / globe (about.js, lazy)

#### AI Projects (`/ai-projects`)
- Controller: `ProjectsController@index`
- Data: all projects where `category = 'ai'`, ordered
- View: filterable grid (JS filter by tech tag), project cards with demo/github links
- 3D: neural network node graph (projects.js, lazy)

#### Experience (`/experience`)
- Controller: `ExperienceController@index`
- Data: all experience entries, ordered
- View: vertical timeline, work + education tabs
- 3D: floating 3D timeline cards (experience.js, lazy)

#### Skills (`/skills`)
- Controller: `SkillsController@index`
- Data: all skills grouped by category
- View: skill bars / radial progress by category
- 3D: constellation / bubble cluster (skills.js, lazy)

#### Portfolio (`/portfolio`)
- Controller: `PortfolioController@index`
- Data: all projects where `category = 'portfolio'`, ordered
- View: filterable masonry / grid, lightbox on click
- 3D: interactive card carousel (portfolio.js, lazy)

#### Contact (`/contact`)
- Controller: `ContactController@index` (GET) + `ContactController@send` (POST)
- POST: validate, sanitize, store in `contact_submissions`, send email via PHP `mail()`
- View: form (name, email, subject, message), honeypot field, CSRF token
- 3D: abstract wave / fluid plane (contact.js, lazy)

#### Blog (`/blog`, `/blog/{slug}`)
- Controller: `BlogController@index` (paginated list), `BlogController@single` (single post)
- Data: published posts, paginated (10/page)
- Single: post content, related posts (same-category, 3 max)
- SEO: BlogPosting schema on single posts
- No 3D on blog (performance, readability)

---

## Phase 5: SEO Engine

**Goal:** Fully automated, per-page SEO output.

### `app/Core/SEO.php`
- Loads `seo_meta` row for current page slug
- Falls back to defaults from `settings` table
- Outputs: `<title>`, `<meta name="description">`, OG tags, Twitter Card tags
- Outputs canonical `<link>`
- Outputs robots meta tag
- Outputs `<script type="application/ld+json">` blocks

### `app/Core/Sitemap.php`
- Triggered on post/project publish via controller
- Queries all public pages, published posts, published projects
- Generates `public/sitemap.xml` with `<url>`, `<lastmod>`, `<changefreq>`, `<priority>`
- Admin can also manually trigger via CMS

### `app/Core/Robots.php`
- Reads rules from `settings` table (`group = 'robots'`)
- Writes `public/robots.txt` on save
- Always appends: `Sitemap: {APP_URL}/sitemap.xml`

---

## Phase 6: Admin CMS — Auth & Dashboard

**Goal:** Secure admin panel foundation.

### 6.1 Auth
- `GET /admin/login` → login form
- `POST /admin/login` → AuthController@login (verify credentials, start session)
- `GET /admin/logout` → AuthController@logout
- All `/admin/*` routes check `Auth::check()` before dispatch

### 6.2 Admin Layout (`app/Views/layouts/admin.php`)
- Sidebar: Dashboard, Posts, Projects, Skills, Experience, Media, SEO, Pages, Settings
- Topbar: user name, logout button
- Flash message area (success/error)
- No Three.js in admin

### 6.3 Dashboard (`GET /admin`)
- Stats cards: total posts, projects, media files, unread contact submissions
- Recent posts table
- Quick-action buttons

---

## Phase 7: Admin CMS — Content Modules

### 7.1 Blog Manager
- List: paginated table of all posts (title, status, date, actions)
- Create/Edit: rich text editor (Quill CDN), title, slug (auto-gen from title), excerpt, featured image picker, status toggle, publish date
- Delete: soft or hard delete with confirm modal
- Triggers `Sitemap::generate()` on publish

### 7.2 Projects Manager
- List: table with category, order, status
- Create/Edit: title, slug, description, tech stack (comma-separated tags), thumbnail (media picker), demo URL, GitHub URL, category (ai/portfolio), order, status
- Drag-to-reorder (updates `order` column via AJAX)

### 7.3 Skills Manager
- List: grouped by category, with proficiency bar preview
- Create/Edit: name, category, proficiency (0–100 slider), icon (icon name or SVG), order
- Reorder within category

### 7.4 Experience Manager
- List: sorted timeline view
- Create/Edit: company, role, description, start date, end date, is_current toggle, type (work/education), order

### 7.5 Media Manager
- Grid view of all uploaded media (thumbnail preview for images)
- Upload: drag-and-drop + file input, validates MIME + size
- Edit: alt text field
- Delete with confirm
- Copy URL to clipboard button
- Used by Blog and Project editors via modal picker

### 7.6 Page Builder
- Per-page block editor (one page at a time, selected from dropdown)
- Available block types: Hero, RichText, ImageText, SkillsGrid, Timeline, Cards, CTA, Spacer
- Each block: type + config JSON (title, body, image, etc.)
- Blocks stored as JSON array in `pages.content_json`
- Drag-to-reorder blocks (vanilla JS sortable)
- Save renders preview link

### 7.7 SEO Manager
- Form per page slug
- Fields: meta title, meta description (with character counters), OG title, OG description, OG image (media picker), canonical URL, robots directive, schema JSON (textarea + validate button)
- SERP preview (live JS render of title + description + URL)
- Save writes to `seo_meta` table

### 7.8 Settings
- Grouped tabs: General (site name, tagline, email), Social Links, SEO Defaults, Robots Rules, Appearance
- All values stored in `settings` key/value table
- Robots tab: textarea for custom rules → triggers `Robots::write()`
- Sitemap tab: manual regenerate button → triggers `Sitemap::generate()`

---

## Phase 8: Frontend 3D Layer

**Goal:** Per-page Three.js scenes, lazy-loaded, performant.

### Loading Strategy
Each public page's PHP view outputs a `data-page` attribute on `<body>`. `core.js` reads this and dynamically imports the correct page JS:

```js
// core.js
const page = document.body.dataset.page;
if (page) {
  import(`/assets/js/pages/${page}.js`).then(m => m.init());
}
```

### Each Page JS Module (`/assets/js/pages/*.js`)
- Exports `init()` function
- Creates `WebGLRenderer` with `antialias`, `alpha: true`
- Sets `setPixelRatio(Math.min(devicePixelRatio, 2))`
- Checks `prefers-reduced-motion` — skip animation if set
- Registers GSAP ScrollTrigger for camera/object animation
- Disposes scene on page unload (prevent memory leaks)

### Per-Page Scene Details

#### home.js
- Particle system: 3000 points, `BufferGeometry`, animated on `requestAnimationFrame`
- Mouse parallax on pointer move
- GSAP: hero text reveal on load, particle scatter on scroll

#### about.js
- Icosahedron wireframe + orbital ring system
- Slow self-rotation, camera dolly on scroll

#### projects.js
- Node graph: spheres connected by `Line` geometry (adjacency matrix)
- Nodes pulse on hover (raycasting)
- GSAP: nodes animate in from center

#### experience.js
- Floating `PlaneGeometry` cards in 3D space
- Camera moves along a path (CatmullRomCurve3) on scroll
- Each card appears as ScrollTrigger passes its position

#### skills.js
- Sphere cluster: `InstancedMesh` of 50+ spheres
- Color-coded by skill category
- Hover: tooltip with skill name (CSS overlay, not 3D text)

#### portfolio.js
- `PlaneGeometry` cards arranged in a circular carousel
- GSAP: rotate carousel on scroll
- Click: expand card to full-screen lightbox (CSS transition)

#### contact.js
- Animated `PlaneGeometry` with vertex displacement (wave shader)
- Slow undulating motion, color shifts on form interaction

---

## Phase 9: Performance & Accessibility

### Core Web Vitals
- LCP: Hero image/canvas visible within 2.5s — preload critical CSS, defer Three.js
- CLS: Reserve canvas space with CSS aspect-ratio before JS loads
- FID/INP: No heavy sync JS on main thread

### Optimizations
- Tailwind CSS purged (only used classes in output)
- Images: WebP with `<picture>` fallback, `loading="lazy"` on below-fold images
- 3D models: Draco-compressed `.glb`, loaded via `GLTFLoader` only when needed
- PHP output buffering + file cache for public pages (5 min TTL)
- `Cache-Control` headers set for static assets via `.htaccess`

### Accessibility
- WCAG 2.1 AA target
- Skip-to-content link
- All images have `alt` text
- Forms have `<label>` associations
- Keyboard-navigable menu
- Reduced motion: Three.js paused, GSAP durations set to 0 if `prefers-reduced-motion: reduce`
- Sufficient color contrast (4.5:1 minimum)

---

## Phase 10: cPanel Deployment

### Pre-deployment Checklist
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `display_errors = 0` in `config/app.php` or `.user.ini`
- [ ] Upload all files (excluding `.env.example`, `PLAN.md`, `.git/`)
- [ ] Upload `.env` with production credentials
- [ ] Create MySQL DB + user in cPanel, import `database/schema.sql`
- [ ] Set `public/uploads/` to `755`
- [ ] Set `storage/` to `755`
- [ ] Point domain document root to `public/`
- [ ] Verify `mod_rewrite` is active
- [ ] Test all routes return 200
- [ ] Run Lighthouse audit, target SEO ≥ 95

### File Permissions
| Path | Permission |
|---|---|
| `public/uploads/` | 755 |
| `storage/cache/` | 755 |
| `storage/logs/` | 755 |
| `.env` | 600 |
| All PHP files | 644 |

---

## Build Order Summary

| Phase | Focus | Dependency |
|---|---|---|
| 0 | Bootstrap & structure | None |
| 1 | MVC Core Engine | Phase 0 |
| 2 | Database Schema | Phase 0 |
| 3 | Domain Models | Phase 1 + 2 |
| 4 | Public Pages | Phase 1 + 3 |
| 5 | SEO Engine | Phase 3 + 4 |
| 6 | Admin Auth + Dashboard | Phase 1 + 2 |
| 7 | CMS Modules | Phase 6 |
| 8 | Three.js 3D Layer | Phase 4 |
| 9 | Performance + A11y | Phase 4 + 8 |
| 10 | cPanel Deployment | All phases |

---

## Open Decisions (Resolve Before Coding)

1. **Rich Text Editor:** Quill vs TipTap — both available via CDN. Quill simpler; TipTap more extensible.
2. **Email sending:** PHP `mail()` vs PHPMailer (no composer = manual include). PHPMailer recommended for reliability.
3. **3D model format:** `.glb` (binary, smaller) preferred over `.gltf`. Draco compression mandatory.
4. **Tailwind:** CDN Play CDN for dev simplicity, or pre-built `app.css` for production (no Node on server).
5. **Blog comments:** In scope or out? Not listed in requirements — excluded by default.
6. **Contact form:** Email only, or also store in DB? Plan includes `contact_submissions` table — store + email.
