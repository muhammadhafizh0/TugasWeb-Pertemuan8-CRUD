<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$pdo = Database::getInstance()->getConnection();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$errors = [];

try {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        set_flash('error', 'Produk tidak ditemukan.');
        redirect('index.php');
    }

    $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
    $suppliers  = $pdo->query('SELECT id, name FROM suppliers ORDER BY name')->fetchAll();
    $values = $product; // form pre-filled

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        [$values, $errors] = validate_product($_POST, $pdo);

        if (!$errors) {
            $upd = $pdo->prepare(
                'UPDATE products SET name = ?, category_id = ?, supplier_id = ?, price = ?, stock = ? WHERE id = ?'
            );
            $upd->execute([
                $values['name'], $values['category_id'], $values['supplier_id'],
                $values['price'], $values['stock'], $id,
            ]);
            set_flash('success', 'Produk "' . $values['name'] . '" berhasil diperbarui.');
            redirect('index.php');
        }
    }
} catch (PDOException $e) {
    error_log('DB Error: ' . $e->getMessage());
    set_flash('error', 'Gagal memperbarui produk. Silakan coba lagi.');
    redirect('index.php');
}

$pageTitle   = 'Edit Produk';
$action      = 'edit.php?id=' . $id;
$submitLabel = 'Perbarui';
require __DIR__ . '/includes/header.php';
?>
<h1>Edit Produk</h1>
<?php require __DIR__ . '/includes/form.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
