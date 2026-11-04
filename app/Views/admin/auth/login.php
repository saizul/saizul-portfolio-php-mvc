<?php use App\Core\Auth; $siteName = config('app.name', 'Saizul Amin'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($siteName) ?> — Admin Login</title>
  <meta name="robots" content="noindex,nofollow">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-950 text-white flex items-center justify-center px-4">
<div class="w-full max-w-md">

  <div class="text-center mb-10">
    <span class="text-2xl font-bold tracking-tight"><?= e($siteName) ?></span>
    <p class="text-gray-500 text-sm mt-1">Admin Panel</p>
  </div>

  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8">
    <h1 class="text-xl font-semibold mb-6">Sign in</h1>

    <?php if (!empty($flash)): ?>
      <?php $cls = $flash['type'] === 'success' ? 'bg-green-950 border-green-800 text-green-300' : 'bg-red-950 border-red-800 text-red-300'; ?>
      <div class="mb-6 px-4 py-3 rounded-lg border text-sm <?= $cls ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= url('/admin/login') ?>" novalidate>
      <?= $csrf_field ?>

      <div class="mb-5">
        <label for="email" class="block text-sm text-gray-400 mb-1.5">Email address</label>
        <input type="email" id="email" name="email"
               value="<?= e($_POST['email'] ?? '') ?>"
               required autofocus autocomplete="email"
               class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-sm
                      focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition">
      </div>

      <div class="mb-6">
        <div class="flex justify-between items-center mb-1.5">
          <label for="password" class="text-sm text-gray-400">Password</label>
          <a href="<?= url('/admin/forgot') ?>" class="text-xs text-gray-500 hover:text-white transition">
            Forgot password?
          </a>
        </div>
        <input type="password" id="password" name="password"
               required autocomplete="current-password"
               class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-sm
                      focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition">
      </div>

      <button type="submit"
              class="w-full py-2.5 bg-white text-black font-semibold rounded-lg text-sm
                     hover:bg-gray-100 active:scale-95 transition-all duration-150">
        Sign in →
      </button>
    </form>
  </div>

  <p class="text-center text-gray-600 text-xs mt-8">© <?= date('Y') ?> <?= e($siteName) ?></p>
</div>
</body>
</html>
