<?php use App\Core\Auth; $siteName = config('app.name', 'Saizul Amin'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($siteName) ?> — Reset Password</title>
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
    <h1 class="text-xl font-semibold mb-2">Set a new password</h1>
    <p class="text-gray-400 text-sm mb-6">
      Minimum 8 characters, at least one uppercase letter and one number.
    </p>

    <?php if (!empty($flash)): ?>
      <?php $cls = $flash['type'] === 'success' ? 'bg-green-950 border-green-800 text-green-300' : 'bg-red-950 border-red-800 text-red-300'; ?>
      <div class="mb-6 px-4 py-3 rounded-lg border text-sm <?= $cls ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= url('/admin/reset/' . $token) ?>" novalidate>
      <?= $csrf_field ?>

      <div class="mb-4">
        <label for="password" class="block text-sm text-gray-400 mb-1.5">New password</label>
        <input type="password" id="password" name="password"
               required autofocus autocomplete="new-password"
               class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-sm
                      focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition">
      </div>

      <div class="mb-6">
        <label for="password_confirm" class="block text-sm text-gray-400 mb-1.5">Confirm new password</label>
        <input type="password" id="password_confirm" name="password_confirm"
               required autocomplete="new-password"
               class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-sm
                      focus:outline-none focus:border-white focus:ring-1 focus:ring-white transition">
      </div>

      <!-- Password strength indicator -->
      <div class="mb-5 space-y-1 text-xs text-gray-500" id="rules">
        <p id="rule-len"  class="flex items-center gap-2"><span>○</span> At least 8 characters</p>
        <p id="rule-case" class="flex items-center gap-2"><span>○</span> At least one uppercase letter</p>
        <p id="rule-num"  class="flex items-center gap-2"><span>○</span> At least one number</p>
        <p id="rule-match"class="flex items-center gap-2"><span>○</span> Passwords match</p>
      </div>

      <button type="submit" id="submit-btn" disabled
              class="w-full py-2.5 bg-white text-black font-semibold rounded-lg text-sm
                     hover:bg-gray-100 active:scale-95 transition-all duration-150
                     disabled:opacity-40 disabled:cursor-not-allowed">
        Update password
      </button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-6">
      <a href="<?= url('/admin/login') ?>" class="hover:text-white transition">← Back to login</a>
    </p>
  </div>

  <p class="text-center text-gray-600 text-xs mt-8">© <?= date('Y') ?> <?= e($siteName) ?></p>
</div>

<script>
const pw  = document.getElementById('password');
const pw2 = document.getElementById('password_confirm');
const btn = document.getElementById('submit-btn');

function mark(id, ok) {
  const el  = document.getElementById(id);
  const dot = el.querySelector('span');
  el.classList.toggle('text-green-400', ok);
  el.classList.toggle('text-gray-500', !ok);
  dot.textContent = ok ? '✓' : '○';
}

function check() {
  const v  = pw.value;
  const v2 = pw2.value;
  const len   = v.length >= 8;
  const upper = /[A-Z]/.test(v);
  const num   = /[0-9]/.test(v);
  const match = v === v2 && v.length > 0;
  mark('rule-len',  len);
  mark('rule-case', upper);
  mark('rule-num',  num);
  mark('rule-match',match);
  btn.disabled = !(len && upper && num && match);
}

pw.addEventListener('input', check);
pw2.addEventListener('input', check);
</script>
</body>
</html>
