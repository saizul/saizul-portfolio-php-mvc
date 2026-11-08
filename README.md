# Saizul Amin Portfolio: Custom PHP MVC

A personal-brand / portfolio platform built from scratch with a lightweight custom PHP MVC framework (router, controllers, models, views), MySQL and an admin panel. Designed for cPanel/shared hosting. See `PLAN.md`, `DESIGN_SYSTEM.md`, `DEPLOYMENT.md`.

Copy `.env.example` to `.env` and fill your values.

## Security

- **Keep `.env` outside the web root.** It holds database credentials and `APP_KEY`. Only `public/` should be reachable from the web: create `.env` in the project root next to `app/` and `config/`, never inside `public/`. Where your host allows it, point the domain's document root at `public/` (see `DEPLOYMENT.md`); if you rely on the root `.htaccess` redirect instead, check that `https://yourdomain.com/.env` returns 403 or 404.
- Never commit `.env` (it is in `.gitignore`); only `.env.example` with placeholder values belongs in the repository.
- Delete `public/install.php` if the setup wizard did not remove itself.

## CI

Every push and pull request runs `php -l` on all PHP files with PHP 8.2, 8.3 and 8.4 (the code uses PHP 8.2 features such as the standalone `true` type; `.github/workflows/php-lint.yml`).

**Author:** [Saizul Amin](https://saizul.com)
