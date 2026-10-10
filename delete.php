<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$pdo = Database::getInstance()->getConnection();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT p.id, p.name, c.name AS category_name
     FROM products p JOIN categories c ON p.category_id = c.id
     WHERE p.id = ?'
);
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'Produk tidak ditemukan.');
    redirect('index.php');
}

// Hapus hanya lewat POST (setelah konfirmasi) + transaction + log aktivitas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $pdo->beginTransaction();

        $log = $pdo->prepare('INSERT INTO activity_logs (action, detail) VALUES (?, ?)');
        $log->execute(['DELETE', sprintf('Produk #%d "%s" dihapus', $product['id'], $product['name'])]);

        $del = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $del->execute([$id]);

        $pdo->commit();
        set_flash('success', 'Produk "' . $product['name'] . '" berhasil dihapus.');
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('DB Error: ' . $e->getMessage());
        set_flash('error', 'Gagal menghapus produk. Tidak ada perubahan disimpan.');
    }
    redirect('index.php');
}

$pageTitle = 'Hapus Produk';
require __DIR__ . '/includes/header.php';
?>
<h1>Konfirmasi Hapus</h1>
<div class="card form">
  <p>Yakin ingin menghapus produk <strong><?= e($product['name']) ?></strong>
     (kategori: <?= e($product['category_name']) ?>)? Tindakan ini tidak dapat dibatalkan.</p>
  <form method="POST" action="delete.php?id=<?= (int) $product['id'] ?>" class="actions">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
    <a class="btn" href="index.php">Batal</a>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
