<?php $flash = get_flash(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? 'Inventaris') ?> - CRUD Inventaris</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <div class="container">
    <a class="brand" href="index.php">📦 Inventaris</a>
    <a class="btn btn-primary" href="create.php">+ Tambah Produk</a>
  </div>
</header>
<main class="container">
<?php if ($flash): ?>
  <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
