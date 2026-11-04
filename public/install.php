<?php
/**
 * install.php — Saizul Amin Portfolio Installation Wizard
 *
 * Steps:
 *  1. Requirements check
 *  2. Database configuration
 *  3. Run migrations + seed
 *  4. Admin account creation
 *  5. Complete (self-deletes)
 *
 * DELETE THIS FILE after installation is complete.
 * The wizard self-deletes on step 5.
 */

define('INSTALLER_VERSION', '1.0.0');
define('ROOT', dirname(__DIR__));

session_start();

$step = (int)($_GET['step'] ?? $_SESSION['install_step'] ?? 1);
$errors = [];
$success = [];

/* ═══════════════════════════════════════════════════════════════
   STEP HANDLERS
═══════════════════════════════════════════════════════════════ */

// Step 2: Save DB config
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    $db = [
        'host' => trim($_POST['db_host'] ?? 'localhost'),
        'port' => trim($_POST['db_port'] ?? '3306'),
        'name' => trim($_POST['db_name'] ?? ''),
        'user' => trim($_POST['db_user'] ?? ''),
        'pass' => $_POST['db_pass'] ?? '',
        'prefix' => '',
    ];
    $siteUrl = rtrim(trim($_POST['site_url'] ?? ''), '/');

    if (!$db['name'] || !$db['user']) {
        $errors[] = 'Database name and username are required.';
    } else {
        // Test connection
        try {
            $pdo = new PDO(
                "mysql:host={$db['host']};port={$db['port']};charset=utf8mb4",
                $db['user'], $db['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$db['name']}`");
            $_SESSION['db'] = $db;
            $_SESSION['site_url'] = $siteUrl;
            $_SESSION['install_step'] = 3;
            header('Location: install.php?step=3');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Database connection failed: ' . htmlspecialchars($e->getMessage());
        }
    }
}

// Step 3: Run migrations
if ($step === 3 && isset($_SESSION['db'])) {
    $db  = $_SESSION['db'];
    $log = [];
    try {
        $pdo = new PDO(
            "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4",
            $db['user'], $db['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Run schema
        $schema = file_get_contents(ROOT . '/database/schema.sql');
        foreach (array_filter(array_map('trim', explode(';', $schema))) as $stmt) {
            if (!empty($stmt) && !str_starts_with($stmt, '--') && !str_starts_with($stmt, 'SET')) {
                try { $pdo->exec($stmt); $log[] = ['ok', substr($stmt, 0, 60) . '…']; }
                catch (PDOException $e) { $log[] = ['warn', $e->getMessage()]; }
            }
        }

        // Run seed
        $seed = file_get_contents(ROOT . '/database/seeds/seed.sql');
        foreach (array_filter(array_map('trim', explode(';', $seed))) as $stmt) {
            if (!empty($stmt) && !str_starts_with($stmt, '--') && !str_starts_with($stmt, 'SET')) {
                try { $pdo->exec($stmt); }
                catch (PDOException $e) { $log[] = ['warn', 'Seed: ' . $e->getMessage()]; }
            }
        }

        $_SESSION['install_step'] = 4;
        $_SESSION['migration_log'] = $log;
    } catch (PDOException $e) {
        $errors[] = 'Migration failed: ' . htmlspecialchars($e->getMessage());
    }
}

// Step 4: Create admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 4) {
    $name  = trim($_POST['admin_name']  ?? '');
    $email = trim($_POST['admin_email'] ?? '');
    $pass  = $_POST['admin_pass']  ?? '';
    $pass2 = $_POST['admin_pass2'] ?? '';

    if (!$name || !$email || !$pass) $errors[] = 'All fields are required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
    elseif (strlen($pass) < 8) $errors[] = 'Password must be at least 8 characters.';
    elseif ($pass !== $pass2) $errors[] = 'Passwords do not match.';
    else {
        $db = $_SESSION['db'];
        try {
            $pdo = new PDO(
                "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4",
                $db['user'], $db['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'admin') ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), name = VALUES(name)");
            $stmt->execute([$name, $email, $hash]);

            // Write .env file
            writeEnv($_SESSION['db'], $_SESSION['site_url'] ?? '', $name, $email);

            // Write robots.txt
            $robotsTxt = "User-agent: *\nAllow: /\nDisallow: /admin\n\nSitemap: " . ($_SESSION['site_url'] ?? '') . "/sitemap.xml\n";
            file_put_contents(ROOT . '/public/robots.txt', $robotsTxt);

            $_SESSION['install_step'] = 5;
            $_SESSION['admin_email']  = $email;
            header('Location: install.php?step=5');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Failed to create admin: ' . htmlspecialchars($e->getMessage());
        }
    }
}

// Step 5: Cleanup
if ($step === 5) {
    // Self-delete installer
    register_shutdown_function(function() {
        @unlink(__FILE__);
        @unlink(ROOT . '/public/install.php');
    });
}

/* ── Write .env ── */
function writeEnv(array $db, string $siteUrl, string $adminName, string $adminEmail): void {
    $appKey = bin2hex(random_bytes(32));
    $content = <<<ENV
APP_NAME="Saizul Amin Portfolio"
APP_ENV=production
APP_URL={$siteUrl}
APP_KEY={$appKey}
APP_DEBUG=false

DB_HOST={$db['host']}
DB_PORT={$db['port']}
DB_DATABASE={$db['name']}
DB_USERNAME={$db['user']}
DB_PASSWORD={$db['pass']}

ADMIN_NAME={$adminName}
ADMIN_EMAIL={$adminEmail}

MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS={$adminEmail}
MAIL_FROM_NAME="Saizul Amin"
ENV;
    file_put_contents(dirname(__DIR__) . '/.env', $content);
}

/* ── Requirements check ── */
function checkRequirements(): array {
    $checks = [];
    $checks[] = ['PHP Version ≥ 8.1', version_compare(PHP_VERSION, '8.1.0', '>='), PHP_VERSION];
    $checks[] = ['PDO Extension',     extension_loaded('pdo'),         extension_loaded('pdo') ? 'Loaded' : 'Missing'];
    $checks[] = ['PDO MySQL',         extension_loaded('pdo_mysql'),   extension_loaded('pdo_mysql') ? 'Loaded' : 'Missing'];
    $checks[] = ['JSON Extension',    extension_loaded('json'),        extension_loaded('json') ? 'Loaded' : 'Missing'];
    $checks[] = ['Mbstring',          extension_loaded('mbstring'),    extension_loaded('mbstring') ? 'Loaded' : 'Missing'];
    $checks[] = ['OpenSSL',           extension_loaded('openssl'),     extension_loaded('openssl') ? 'Loaded' : 'Missing'];
    $checks[] = ['FileInfo',          extension_loaded('fileinfo'),    extension_loaded('fileinfo') ? 'Loaded' : 'Missing'];
    $rootWrite = is_writable(dirname(__DIR__));
    $checks[] = ['Root Dir Writable', $rootWrite, $rootWrite ? 'Writable' : 'Not writable (chmod 755)'];
    $pubWrite  = is_writable(dirname(__DIR__) . '/public/uploads');
    if (!is_dir(dirname(__DIR__) . '/public/uploads')) @mkdir(dirname(__DIR__) . '/public/uploads', 0755, true);
    $pubWrite  = is_writable(dirname(__DIR__) . '/public/uploads');
    $checks[] = ['Uploads Dir Writable', $pubWrite, $pubWrite ? 'Writable' : 'Not writable — run: chmod 755 public/uploads'];
    $storeWrite = is_writable(dirname(__DIR__) . '/storage');
    if (!is_dir(dirname(__DIR__) . '/storage')) @mkdir(dirname(__DIR__) . '/storage', 0755, true);
    $storeWrite = is_writable(dirname(__DIR__) . '/storage');
    $checks[] = ['Storage Dir Writable', $storeWrite, $storeWrite ? 'Writable' : 'Not writable — run: chmod 755 storage'];
    return $checks;
}

$requirements = checkRequirements();
$allPassed    = !in_array(false, array_column($requirements, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Installation Wizard — Saizul Amin Portfolio</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;background:#080c10;color:#e2e8f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:40px 20px}
.wizard{width:100%;max-width:640px}
.wizard-header{text-align:center;margin-bottom:40px}
.wizard-logo{font-size:2.5rem;font-weight:900;letter-spacing:-0.04em;color:#fff;margin-bottom:8px}
.wizard-logo span{color:#c0392b}
.wizard-subtitle{color:#64748b;font-size:0.875rem}
.steps{display:flex;gap:0;margin-bottom:40px;border-radius:12px;overflow:hidden;border:1px solid #1e3040}
.step-item{flex:1;padding:12px 8px;text-align:center;font-size:0.75rem;font-weight:600;color:#475569;background:#0d1b2a;border-right:1px solid #1e3040;transition:all 0.2s}
.step-item:last-child{border-right:none}
.step-item.active{background:#c0392b22;color:#e2e8f0;border-color:#c0392b44}
.step-item.done{background:#6ee7b722;color:#6ee7b7}
.step-num{display:block;font-size:1rem;font-weight:800;margin-bottom:2px}
.card{background:#0d1b2a;border:1px solid #1e3040;border-radius:16px;padding:32px}
.card-title{font-size:1.25rem;font-weight:700;margin-bottom:8px}
.card-subtitle{color:#64748b;font-size:0.875rem;margin-bottom:28px}
.form-group{margin-bottom:18px}
label{display:block;font-size:0.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px}
input,select{width:100%;background:#080c10;border:1px solid #1e3040;color:#e2e8f0;padding:10px 14px;border-radius:8px;font-size:0.9375rem;font-family:inherit;transition:border-color 0.15s}
input:focus,select:focus{outline:none;border-color:#c0392b}
.form-row{display:grid;grid-template-columns:2fr 1fr;gap:12px}
.btn-primary{width:100%;background:#c0392b;color:#fff;border:none;padding:14px;border-radius:8px;font-size:1rem;font-weight:700;cursor:pointer;transition:background 0.2s;margin-top:8px}
.btn-primary:hover{background:#e74c3c}
.btn-primary:disabled{background:#1e3040;color:#475569;cursor:not-allowed}
.check-row{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #1e3040}
.check-row:last-child{border-bottom:none}
.check-label{font-size:0.875rem;color:#94a3b8}
.check-val{font-size:0.8125rem;font-family:monospace}
.pass{color:#6ee7b7}.fail{color:#f87171}.warn{color:#fbbf24}
.alert-error{background:#f8717122;border:1px solid #f8717144;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#f87171;font-size:0.875rem}
.alert-success{background:#6ee7b722;border:1px solid #6ee7b744;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#6ee7b7;font-size:0.875rem}
.migration-log{background:#080c10;border-radius:8px;padding:16px;max-height:200px;overflow-y:auto;font-family:monospace;font-size:0.75rem;margin-bottom:16px}
.log-ok{color:#6ee7b7}.log-warn{color:#fbbf24}
.complete-icon{font-size:4rem;text-align:center;margin-bottom:16px}
.hint{font-size:0.8125rem;color:#475569;margin-top:6px}
a.link{color:#6ee7b7;text-decoration:none}a.link:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="wizard">
    <div class="wizard-header">
        <div class="wizard-logo">SAIZUL<span>.</span></div>
        <div class="wizard-subtitle">Portfolio Installation Wizard v<?= INSTALLER_VERSION ?></div>
    </div>

    <!-- Step indicator -->
    <div class="steps">
        <?php
        $stepLabels = ['Requirements','Database','Migrate','Admin','Complete'];
        for ($i = 1; $i <= 5; $i++):
            $cls = $i === $step ? 'active' : ($i < $step ? 'done' : '');
        ?>
        <div class="step-item <?= $cls ?>">
            <span class="step-num"><?= $i < $step ? '✓' : $i ?></span>
            <?= $stepLabels[$i-1] ?>
        </div>
        <?php endfor; ?>
    </div>

    <?php foreach ($errors as $e): ?>
    <div class="alert-error">⚠ <?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <!-- ─── STEP 1: Requirements ─── -->
    <?php if ($step === 1): ?>
    <div class="card">
        <div class="card-title">System Requirements</div>
        <div class="card-subtitle">Checking your server environment before installation.</div>
        <?php foreach ($requirements as [$label, $pass, $val]): ?>
        <div class="check-row">
            <span class="check-label"><?= htmlspecialchars($label) ?></span>
            <span class="check-val <?= $pass ? 'pass' : 'fail' ?>"><?= $pass ? '✓ ' : '✗ ' ?><?= htmlspecialchars($val) ?></span>
        </div>
        <?php endforeach; ?>
        <form method="GET" action="install.php">
            <input type="hidden" name="step" value="2">
            <button type="submit" class="btn-primary" <?= !$allPassed ? 'disabled' : '' ?>>
                <?= $allPassed ? 'Continue to Database Setup →' : 'Fix issues above to continue' ?>
            </button>
        </form>
    </div>

    <!-- ─── STEP 2: Database ─── -->
    <?php elseif ($step === 2): ?>
    <div class="card">
        <div class="card-title">Database Configuration</div>
        <div class="card-subtitle">Enter your MySQL database credentials. Find these in cPanel → MySQL Databases.</div>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>DB Host</label>
                    <input type="text" name="db_host" value="localhost" required>
                </div>
                <div class="form-group">
                    <label>Port</label>
                    <input type="text" name="db_port" value="3306">
                </div>
            </div>
            <div class="form-group">
                <label>Database Name</label>
                <input type="text" name="db_name" placeholder="saizul_portfolio" required>
            </div>
            <div class="form-group">
                <label>Database Username</label>
                <input type="text" name="db_user" placeholder="saizul_user" required>
            </div>
            <div class="form-group">
                <label>Database Password</label>
                <input type="password" name="db_pass" placeholder="••••••••">
            </div>
            <div class="form-group">
                <label>Site URL</label>
                <input type="url" name="site_url" placeholder="https://saizulamin.com" required>
                <p class="hint">No trailing slash. Used for canonical URLs and sitemaps.</p>
            </div>
            <button type="submit" class="btn-primary">Test Connection & Continue →</button>
        </form>
    </div>

    <!-- ─── STEP 3: Migrate ─── -->
    <?php elseif ($step === 3): ?>
    <div class="card">
        <div class="card-title">Running Migrations</div>
        <div class="card-subtitle">Creating database tables and importing demo content.</div>
        <?php if (!empty($_SESSION['migration_log'])): ?>
        <div class="migration-log">
            <?php foreach ($_SESSION['migration_log'] as [$type, $msg]): ?>
            <div class="log-<?= $type ?>"><?= $type === 'ok' ? '✓' : '⚠' ?> <?= htmlspecialchars($msg) ?></div>
            <?php endforeach; ?>
        </div>
        <div class="alert-success">✓ Database setup complete. Tables created and demo data imported.</div>
        <a href="install.php?step=4"><button class="btn-primary">Create Admin Account →</button></a>
        <?php else: ?>
        <div class="alert-error">Migration error. Check DB connection.</div>
        <a href="install.php?step=2"><button class="btn-primary">← Back to Database</button></a>
        <?php endif; ?>
    </div>

    <!-- ─── STEP 4: Admin ─── -->
    <?php elseif ($step === 4): ?>
    <div class="card">
        <div class="card-title">Create Admin Account</div>
        <div class="card-subtitle">This will be your login to the CMS dashboard at <code>/admin</code>.</div>
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="admin_name" value="Saizul Amin" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="admin_email" value="saizulamin@gmail.com" required>
            </div>
            <div class="form-group">
                <label>Password (min 8 characters)</label>
                <input type="password" name="admin_pass" required minlength="8">
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="admin_pass2" required minlength="8">
            </div>
            <button type="submit" class="btn-primary">Create Account & Finish →</button>
        </form>
    </div>

    <!-- ─── STEP 5: Complete ─── -->
    <?php elseif ($step === 5): ?>
    <div class="card" style="text-align:center;">
        <div class="complete-icon">🎉</div>
        <div class="card-title" style="text-align:center;">Installation Complete!</div>
        <p style="color:#94a3b8;margin:12px 0 28px;line-height:1.7;">
            Your portfolio is installed and ready.<br>
            The installer file has been deleted for security.
        </p>
        <div style="display:grid;gap:12px;">
            <a href="/" class="btn-primary" style="display:block;text-decoration:none;text-align:center;">View Your Portfolio →</a>
            <a href="/admin" style="display:block;background:#1a2f35;color:#6ee7b7;border:1px solid #264d54;border-radius:8px;padding:14px;text-decoration:none;font-weight:600;">
                Go to Admin Dashboard →
            </a>
        </div>
        <p style="margin-top:24px;font-size:0.8125rem;color:#475569;">
            Admin login: <strong><?= htmlspecialchars($_SESSION['admin_email'] ?? '') ?></strong>
        </p>
    </div>
    <?php endif; ?>

</div>
</body>
</html>
