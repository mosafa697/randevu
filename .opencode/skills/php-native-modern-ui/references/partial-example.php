<?php
/*
  Example partials/header.php
  ---------------------------
  Usage from any page:
    <?php $activeNav = 'dashboard'; require __DIR__ . '/../partials/header.php'; ?>

  This is the ONE place <head>, nav markup, and stylesheet links live.
  No page should redeclare any of this.
*/
$activeNav = $activeNav ?? '';
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? 'App') ?></title>
  <link rel="stylesheet" href="/assets/css/tokens.css">
  <link rel="stylesheet" href="/assets/css/base.css">
  <link rel="stylesheet" href="/assets/css/components.css">
</head>
<body>
  <header class="site-header">
    <nav class="site-nav" aria-label="Primary">
      <a href="/" class="site-nav__brand">Your App</a>
      <ul class="site-nav__links">
        <li><a href="/dashboard.php" aria-current="<?= $activeNav === 'dashboard' ? 'page' : 'false' ?>">Dashboard</a></li>
        <li><a href="/invoices.php" aria-current="<?= $activeNav === 'invoices' ? 'page' : 'false' ?>">Invoices</a></li>
        <li><a href="/settings.php" aria-current="<?= $activeNav === 'settings' ? 'page' : 'false' ?>">Settings</a></li>
      </ul>
    </nav>
  </header>

<?php require __DIR__ . '/flash.php'; /* shared success/error banner, optional */ ?>

<!--
  partials/footer.php would close out like this:

  </body>
  </html>
-->
