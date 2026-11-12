# Deployment Guide — Saizul Amin Portfolio
## cPanel Shared Hosting — Step-by-Step

---

## Prerequisites

| Requirement | Minimum |
|---|---|
| PHP | 8.1+ (set via MultiPHP Manager) |
| MySQL | 5.7+ or MariaDB 10.4+ |
| mod_rewrite | Enabled (standard on cPanel) |
| SSL Certificate | Required (Let's Encrypt via cPanel) |
| Disk Space | 100 MB minimum |

---

## 1. Prepare Files Locally

```bash
# Zip the project (exclude dev files)
zip -r saizul-portfolio.zip . \
  --exclude "*.git*" \
  --exclude "node_modules/*" \
  --exclude ".DS_Store" \
  --exclude "*.log"
```

---

## 2. Upload to cPanel

1. Log in to **cPanel → File Manager**
2. Navigate to `public_html` (or your subdomain folder)
3. Upload `saizul-portfolio.zip`
4. Extract — you should now have `public_html/` containing the project root
5. Ensure the structure is:
   ```
   public_html/
   ├── app/
   ├── config/
   ├── database/
   ├── public/          ← this is the web root
   ├── storage/
   ├── .htaccess        ← root redirect to public/
   └── .env             ← create this (step 5)
   ```

> **cPanel document root tip:** If your domain's document root is `public_html`, the root `.htaccess` redirects all traffic to `public_html/public/`. If cPanel allows setting document root to `public_html/public`, do that instead and skip the root `.htaccess`.

---

## 3. Set PHP Version

1. cPanel → **MultiPHP Manager**
2. Select your domain
3. Set PHP version to **8.1** or **8.3**
4. Save

---

## 4. Create Database

1. cPanel → **MySQL Databases**
2. Create database: `yourusername_portfolio`
3. Create user: `yourusername_portuser` with a strong password
4. Add user to database with **All Privileges**
5. Note down: host (`localhost`), database name, username, password

---

## 5. Create .env File

In File Manager, create `.env` in the project root (NOT inside `public/`):

```env
APP_NAME="Saizul Amin Portfolio"
APP_ENV=production
APP_URL=https://saizulamin.com
APP_KEY=<run: php -r "echo bin2hex(random_bytes(32));"> 
APP_DEBUG=false

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=yourusername_portfolio
DB_USERNAME=yourusername_portuser
DB_PASSWORD=your_database_password

MAIL_HOST=mail.saizulamin.com
MAIL_PORT=587
MAIL_USERNAME=hello@saizulamin.com
MAIL_PASSWORD=your_email_password
MAIL_FROM_ADDRESS=hello@saizulamin.com
MAIL_FROM_NAME="Saizul Amin"
```

> **Generate APP_KEY:** SSH into your server and run:
> `php -r "echo bin2hex(random_bytes(32));"`

---

## 6. Set Permissions

Via cPanel File Manager or SSH:

```bash
# Directories
find . -type d -exec chmod 755 {} \;

# Files
find . -type f -exec chmod 644 {} \;

# Writable directories
chmod 755 storage/
chmod 755 storage/cache/
chmod 755 storage/logs/
chmod 755 storage/sitemaps/
chmod 755 storage/ratelimit/   # contact-form rate limiter (created on first use if storage/ is writable)
chmod 755 public/uploads/

# Protect sensitive files
chmod 600 .env
```

---

## 7. Run Installation Wizard

1. Visit `https://yourdomain.com/install.php`
2. **Step 1** — Requirements check (all should pass)
3. **Step 2** — Enter DB credentials from step 4
4. **Step 3** — Migrations run automatically (tables + demo data)
5. **Step 4** — Create admin account
6. **Step 5** — Done! Wizard self-deletes

> If the wizard doesn't self-delete, manually delete `public/install.php`

---

## 8. Configure SSL (HTTPS)

1. cPanel → **SSL/TLS** → **Let's Encrypt SSL**
2. Issue certificate for your domain + www
3. After certificate is active, uncomment in `public/.htaccess`:
   ```apache
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
   
   # And enable HSTS:
   Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
   ```

---

## 9. DNS Configuration

| Record | Type | Value |
|---|---|---|
| `@` | A | Your server IP |
| `www` | CNAME | `yourdomain.com` |

---

## 10. Post-Deploy Checklist

### Functionality
- [ ] Home page loads with 3D scene
- [ ] All 8 pages accessible
- [ ] Contact form submits successfully
- [ ] Admin login works at `/admin`
- [ ] Image uploads work in Media Manager
- [ ] Blog post create/publish works
- [ ] Sitemap accessible at `/sitemap.xml`
- [ ] Robots.txt accessible at `/robots.txt`

### Performance (run Lighthouse)
- [ ] Performance score ≥ 90
- [ ] SEO score ≥ 95
- [ ] Accessibility score ≥ 85
- [ ] Best Practices score ≥ 90
- [ ] Core Web Vitals: LCP < 2.5s, FID < 100ms, CLS < 0.1

### Security
- [ ] `.env` not publicly accessible (test: visit `yourdomain.com/.env`)
- [ ] `/admin` redirects unauthenticated users to login
- [ ] `/app`, `/config`, `/database`, `/storage` return 403
- [ ] `install.php` is deleted
- [ ] HTTPS redirect working
- [ ] Security headers present (check with [securityheaders.com](https://securityheaders.com))

### SEO
- [ ] Submit sitemap to Google Search Console
- [ ] Submit sitemap to Bing Webmaster Tools
- [ ] Verify Open Graph tags with [opengraph.xyz](https://www.opengraph.xyz)
- [ ] Verify Twitter Card with Twitter Card Validator
- [ ] Test structured data with Google Rich Results Test

---

## Troubleshooting

### 500 Internal Server Error
- Check `storage/logs/php-errors.log`
- Temporarily set `APP_DEBUG=true` in `.env`
- Verify `.htaccess` mod_rewrite syntax

### 404 on all pages (except home)
- Verify `mod_rewrite` is enabled
- Check `.htaccess` is in `public/` directory
- Ensure `RewriteBase /` is correct for your setup

### Database connection failed
- Verify `.env` DB credentials
- Confirm DB user has privileges
- Try `DB_HOST=127.0.0.1` instead of `localhost`

### 3D scenes not loading
- Check browser console for CORS errors
- Verify `https://unpkg.com` is accessible from client
- Check Content Security Policy headers

### File uploads failing
- Verify `public/uploads/` is `chmod 755`
- Check PHP `upload_max_filesize` (set to 10M in php.ini)
- Check `post_max_size` ≥ `upload_max_filesize`

---

## PHP.ini Recommendations

Create `public/.user.ini` (cPanel respects this):

```ini
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 60
max_input_vars = 3000
memory_limit = 256M
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0
opcache.revalidate_freq = 0
```

---

## Maintenance

### Update content
- Log in at `/admin`
- Use Page Builder, Posts, Projects, Media, SEO modules

### Regenerate sitemap
- Admin → SEO → Click "↺ Regenerate" button
- Or POST to `/admin/sitemap/regenerate`

### Backup database
- cPanel → **Backup** → Download MySQL database backup
- Schedule weekly automated backups in cPanel

### Clear cache
- Delete contents of `storage/cache/`
- Or SSH: `rm -rf storage/cache/*`
