# Changelog

All notable changes to this project are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added
- Contact form rate limit: 3 accepted messages per client IP per 10 minutes, backed by a new file-based sliding-window `App\Core\RateLimiter` (`storage/ratelimit/`, fails open if storage is unwritable). Optional `TRUSTED_PROXY_HEADER` for sites behind Cloudflare.
- `App\Core\Validator` with `required`, `email`, `min`, `max`, `in` and `single_line` rules; the contact form now shows per-field errors and keeps the visitor's input.
- Minimum fill-time bot trap on the contact form, next to the existing honeypot.
- PHPUnit test suite (`composer test`) for the router, validator, rate limiter, contact-form rules and sanitize helpers, run in CI on PHP 8.2, 8.3 and 8.4.

### Changed
- Contact notifications are sent `From:` the site's `MAIL_FROM_ADDRESS` with the visitor in `Reply-To:` and CR/LF stripped from all header values (previously the visitor's address was used as `From:` and the free-text subject went into the `Subject:` header). The subject must now be one of the dropdown topics.
- Router: `_method` override only switches POST to PUT or DELETE, and literal route segments are regex-quoted (`/sitemap.xml` no longer matches `/sitemapXxml`).

### Fixed
- Router no longer throws a `TypeError` for malformed request URIs such as `//`.
- `database/schema.sql` (used by the install wizard) now defines `contact_submissions` with the `ip`, `user_agent` and `is_read` columns the contact form and admin dashboard use; fresh installs previously failed to save contact messages. Existing installs created from the old schema need those columns added, e.g. `ALTER TABLE contact_submissions CHANGE ip_address ip VARCHAR(45) NULL, ADD user_agent VARCHAR(500) NULL, ADD is_read TINYINT(1) NOT NULL DEFAULT 0, ADD is_replied TINYINT(1) NOT NULL DEFAULT 0, ADD replied_at DATETIME NULL;`.
