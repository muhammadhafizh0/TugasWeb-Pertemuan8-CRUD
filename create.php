<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$pdo = Database::getInstance()->getConnection();
$errors = [];
$values = [];

try {
    $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
    $suppliers  = $pdo->query('SELECT id, name FROM suppliers ORDER BY name')->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        [$values, $errors] = validate_product($_POST, $pdo);

        if (!$errors) {
            // PREPARED STATEMENT
            $stmt = $pdo->prepare(
                'INSERT INTO products (name, category_id, supplier_id, price, stock) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $values['name'], $values['category_id'], $values['supplier_id'],
                $values['price'], $values['stock'],
            ]);
            set_flash('success', 'Produk "' . $values['name'] . '" berhasil ditambahkan.');
            redirect('index.php'); // Post/Redirect/Get
        }
    }
} catch (PDOException $e) {
    error_log('DB Error: ' . $e->getMessage());
    set_flash('error', 'Gagal menyimpan produk. Silakan coba lagi.');
    redirect('index.php');
}

$pageTitle    = 'Tambah Produk';
$action       = 'create.php';
$submitLabel  = 'Simpan';
require __DIR__ . '/includes/header.php';
?>
<h1>Tambah Produk</h1>
<?php require __DIR__ . '/includes/form.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
