# Saizul Amin Portfolio: Custom PHP MVC

A personal-brand / portfolio platform built from scratch with a lightweight custom PHP MVC framework (router, controllers, models, views), MySQL and an admin panel. Designed for cPanel/shared hosting. See `PLAN.md`, `DESIGN_SYSTEM.md`, `DEPLOYMENT.md`.

Copy `.env.example` to `.env` and fill your values.

## Security

- **Keep `.env` outside the web root.** It holds database credentials and `APP_KEY`. Only `public/` should be reachable from the web: create `.env` in the project root next to `app/` and `config/`, never inside `public/`. Where your host allows it, point the domain's document root at `public/` (see `DEPLOYMENT.md`); if you rely on the root `.htaccess` redirect instead, check that `https://yourdomain.com/.env` returns 403 or 404.
- Never commit `.env` (it is in `.gitignore`); only `.env.example` with placeholder values belongs in the repository.
- Delete `public/install.php` if the setup wizard did not remove itself.

## Contact form protection

`POST /contact` (`app/Controllers/ContactController.php`) is hardened against spam and abuse:

- **CSRF token** on every submission (as before).
- **Bot traps:** a hidden honeypot field and a minimum fill time (3 seconds after the form was rendered). Bots get a normal "sent" response and nothing is stored or mailed.
- **Rate limit:** at most 3 accepted messages per client IP per 10 minutes (`App\Core\RateLimiter`, a file-based sliding window in `storage/ratelimit/`, no database or Redis needed). If storage is not writable the limiter fails open and logs the problem. The IP comes from `REMOTE_ADDR`; behind Cloudflare or another proxy you control, set `TRUSTED_PROXY_HEADER=HTTP_CF_CONNECTING_IP` in `.env`.
- **Validation** with `App\Core\Validator`: required fields, a valid email, length limits that match the `contact_submissions` columns (name/email 150, message 10 to 5000 characters), subject restricted to the dropdown topics, and no line breaks in single-line fields. Errors are shown next to each field and the form keeps what the visitor typed.
- **Mail header safety:** the notification is sent `From:` your own `MAIL_FROM_ADDRESS` (so SPF/DMARC pass) with the visitor in `Reply-To:`, and every header value is stripped of CR/LF. Set the receiving inbox with `MAIL_TO`.

`App\Core\Validator` is generic; rules are `required`, `email`, `min:N`, `max:N`, `in:a,b,c` and `single_line`:

```php
$v = Validator::make($_POST, ['email' => 'required|email|max:150']);
if ($v->fails()) { $errors = $v->errors(); } else { $clean = $v->validated(); }
```

## Tests

Unit tests use PHPUnit (dev dependency only; nothing is needed on the server):

```bash
composer install
composer test        # or: vendor/bin/phpunit
```

They cover the router (parameters, method override, literal dots, 404s, malformed URIs), the validator, the rate limiter (sliding window with an injected clock) and the contact-form rules. `vendor/` is gitignored.

## CI

Every push and pull request runs `php -l` on all PHP files and the PHPUnit suite with PHP 8.2, 8.3 and 8.4 (the code uses PHP 8.2 features such as the standalone `true` type; `.github/workflows/php-lint.yml`).

**Author:** [Saizul Amin](https://saizul.com)
