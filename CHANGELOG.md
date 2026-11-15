# Changelog

All notable changes to this project are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Security
- Media uploads: the stored file extension is now derived from the sniffed MIME type instead of the client filename, so an image/PHP polyglot named `shell.php` can no longer be stored with a `.php` extension under `public/uploads/`.
- New `App\Core\UploadPolicy`: upload folder names are restricted to `[a-z0-9_-]` segments (no `..` traversal), SVG uploads with scripts, event handlers, external references or entity declarations are rejected, and raster images are capped at 12,000 px per side and 40 megapixels.
- `public/uploads/.htaccess` is created on first upload to disable PHP/CGI handlers and sandbox SVGs, and `public/.htaccess` denies script extensions under `/uploads/`.
- Deleting media only removes files that resolve inside `public/uploads/`.

### Added
- `Upload::inspect()` validates a file without moving it; `Upload::lastRejected()` lists files skipped by a bulk upload with the reason, shown in the admin flash message and the AJAX response.
- PHPUnit tests for the upload policy and file inspection.
- Contact form rate limit: 3 accepted messages per client IP per 10 minutes, backed by a new file-based sliding-window `App\Core\RateLimiter` (`storage/ratelimit/`, fails open if storage is unwritable). Optional `TRUSTED_PROXY_HEADER` for sites behind Cloudflare.
- `App\Core\Validator` with `required`, `email`, `min`, `max`, `in` and `single_line` rules; the contact form now shows per-field errors and keeps the visitor's input.
- Minimum fill-time bot trap on the contact form, next to the existing honeypot.
- PHPUnit test suite (`composer test`) for the router, validator, rate limiter, contact-form rules and sanitize helpers, run in CI on PHP 8.2, 8.3 and 8.4.

### Changed
- Contact notifications are sent `From:` the site's `MAIL_FROM_ADDRESS` with the visitor in `Reply-To:` and CR/LF stripped from all header values (previously the visitor's address was used as `From:` and the free-text subject went into the `Subject:` header). The subject must now be one of the dropdown topics.
- Router: `_method` override only switches POST to PUT or DELETE, and literal route segments are regex-quoted (`/sitemap.xml` no longer matches `/sitemapXxml`).

### Fixed
- Media library controller called methods that do not exist (`Auth::requireAdmin()`, `Auth::verifyCsrf()`, `Auth::csrfToken()`), passed flash text as the HTTP status argument of `redirect()`, and type-hinted route ids as `int`; every media action failed. It now uses the base controller's role guard, CSRF check and flash messages.
- Router no longer throws a `TypeError` for malformed request URIs such as `//`.
- `database/schema.sql` (used by the install wizard) now defines `contact_submissions` with the `ip`, `user_agent` and `is_read` columns the contact form and admin dashboard use; fresh installs previously failed to save contact messages. Existing installs created from the old schema need those columns added, e.g. `ALTER TABLE contact_submissions CHANGE ip_address ip VARCHAR(45) NULL, ADD user_agent VARCHAR(500) NULL, ADD is_read TINYINT(1) NOT NULL DEFAULT 0, ADD is_replied TINYINT(1) NOT NULL DEFAULT 0, ADD replied_at DATETIME NULL;`.
