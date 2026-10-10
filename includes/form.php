<?php /** Form bersama untuk create & edit. Variabel: $values, $categories, $suppliers, $errors, $action, $submitLabel */ ?>
<?php if (!empty($errors)): ?>
  <div class="alert alert-error">
    <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<form method="POST" action="<?= e($action) ?>" class="card form">
  <?= csrf_field() ?>

  <label>Nama Produk
    <input type="text" name="name" maxlength="100" required value="<?= e($values['name'] ?? '') ?>">
  </label>

  <label>Kategori
    <select name="category_id" required>
      <option value="">-- Pilih kategori --</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= (int) $c['id'] ?>" <?= (int) ($values['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>>
          <?= e($c['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <label>Supplier
    <select name="supplier_id" required>
      <option value="">-- Pilih supplier --</option>
      <?php foreach ($suppliers as $s): ?>
        <option value="<?= (int) $s['id'] ?>" <?= (int) ($values['supplier_id'] ?? 0) === (int) $s['id'] ? 'selected' : '' ?>>
          <?= e($s['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <div class="row">
    <label>Harga (Rp)
      <input type="number" name="price" min="0" step="0.01" required value="<?= e((string) ($values['price'] ?? '')) ?>">
    </label>
    <label>Stok
      <input type="number" name="stock" min="0" step="1" required value="<?= e((string) ($values['stock'] ?? '0')) ?>">
    </label>
  </div>

  <div class="actions">
    <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
    <a class="btn" href="index.php">Batal</a>
  </div>
</form>
