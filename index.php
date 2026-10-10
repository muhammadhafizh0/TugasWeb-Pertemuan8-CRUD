<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$pdo = Database::getInstance()->getConnection();

// Pencarian (bonus) + pagination (bonus)
$q       = trim((string) ($_GET['q'] ?? ''));
$perPage = 5;
$page    = max(1, (int) ($_GET['page'] ?? 1));
$like    = '%' . $q . '%';

try {
    $count = $pdo->prepare('SELECT COUNT(*) FROM products WHERE name LIKE ?');
    $count->execute([$like]);
    $total      = (int) $count->fetchColumn();
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page       = min($page, $totalPages);
    $offset     = ($page - 1) * $perPage;

    // JOIN 2 tabel: products + categories + suppliers
    $sql = "SELECT p.id, p.name, p.price, p.stock,
                   c.name AS category_name, s.name AS supplier_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
            JOIN suppliers  s ON p.supplier_id = s.id
            WHERE p.name LIKE :q
            ORDER BY p.id DESC
            LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':q', $like);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('DB Error: ' . $e->getMessage());
    $products = [];
    $total = 0;
    $totalPages = 1;
    set_flash('error', 'Terjadi kesalahan database.');
}

$pageTitle = 'Daftar Produk';
require __DIR__ . '/includes/header.php';
?>
<h1>Daftar Produk</h1>

<form method="GET" class="search">
  <input type="search" name="q" placeholder="Cari nama produk..." value="<?= e($q) ?>">
  <button class="btn" type="submit">Cari</button>
  <?php if ($q !== ''): ?><a class="btn" href="index.php">Reset</a><?php endif; ?>
</form>

<div class="card table-wrap">
<table>
  <thead>
    <tr><th>#</th><th>Nama</th><th>Kategori</th><th>Supplier</th><th class="num">Harga</th><th class="num">Stok</th><th>Aksi</th></tr>
  </thead>
  <tbody>
  <?php if (!$products): ?>
    <tr><td colspan="7" class="empty">Tidak ada data produk.</td></tr>
  <?php endif; ?>
  <?php foreach ($products as $p): ?>
    <tr>
      <td><?= (int) $p['id'] ?></td>
      <td><?= e($p['name']) ?></td>
      <td><span class="badge"><?= e($p['category_name']) ?></span></td>
      <td><?= e($p['supplier_name']) ?></td>
      <td class="num"><?= e(rupiah($p['price'])) ?></td>
      <td class="num"><?= (int) $p['stock'] ?></td>
      <td class="actions">
        <a class="btn btn-sm" href="edit.php?id=<?= (int) $p['id'] ?>">Edit</a>
        <a class="btn btn-sm btn-danger" href="delete.php?id=<?= (int) $p['id'] ?>">Hapus</a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<nav class="pagination">
  <span><?= $total ?> produk · halaman <?= $page ?>/<?= $totalPages ?></span>
  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a class="btn btn-sm <?= $i === $page ? 'btn-primary' : '' ?>"
       href="?page=<?= $i ?>&q=<?= urlencode($q) ?>"><?= $i ?></a>
  <?php endfor; ?>
</nav>
<?php require __DIR__ . '/includes/footer.php'; ?>
