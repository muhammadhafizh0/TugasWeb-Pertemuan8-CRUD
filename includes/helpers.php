<?php
declare(strict_types=1);

session_start();

/** Escape output HTML (pencegahan XSS). */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Flash message (pola Post/Redirect/Get). */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/** Token CSRF sederhana untuk semua form POST. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) $sent)) {
        http_response_code(403);
        exit('Token tidak valid.');
    }
}

function rupiah(float|string $n): string
{
    return 'Rp ' . number_format((float) $n, 0, ',', '.');
}

/** Validasi input produk. Kembalikan [data, errors]. */
function validate_product(array $in, PDO $pdo): array
{
    $data = [
        'name'        => trim((string) ($in['name'] ?? '')),
        'category_id' => (int) ($in['category_id'] ?? 0),
        'supplier_id' => (int) ($in['supplier_id'] ?? 0),
        'price'       => (float) ($in['price'] ?? 0),
        'stock'       => (int) ($in['stock'] ?? 0),
    ];
    $errors = [];

    if ($data['name'] === '' || mb_strlen($data['name']) > 100) {
        $errors[] = 'Nama produk wajib diisi (maks. 100 karakter).';
    }
    if ($data['price'] < 0) {
        $errors[] = 'Harga tidak boleh negatif.';
    }
    if ($data['stock'] < 0) {
        $errors[] = 'Stok tidak boleh negatif.';
    }

    $st = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = ?');
    $st->execute([$data['category_id']]);
    if (!$st->fetchColumn()) {
        $errors[] = 'Kategori tidak valid.';
    }
    $st = $pdo->prepare('SELECT COUNT(*) FROM suppliers WHERE id = ?');
    $st->execute([$data['supplier_id']]);
    if (!$st->fetchColumn()) {
        $errors[] = 'Supplier tidak valid.';
    }

    return [$data, $errors];
}
