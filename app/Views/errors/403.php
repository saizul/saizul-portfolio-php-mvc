<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>403 — Forbidden</title>
  <style>
    body { margin:0; background:#0a0a0a; color:#fff; font-family:system-ui,sans-serif;
           display:flex; align-items:center; justify-content:center; min-height:100vh; text-align:center; }
    h1   { font-size:clamp(4rem,15vw,12rem); font-weight:800; margin:0; opacity:.1; }
    p    { font-size:1.25rem; opacity:.6; margin-top:-2rem; }
    a    { color:#fff; text-underline-offset:4px; }
  </style>
</head>
<body>
  <div>
    <h1>403</h1>
    <p>Access denied. <a href="<?= url('/admin') ?>">Go to dashboard →</a></p>
  </div>
</body>
</html>
