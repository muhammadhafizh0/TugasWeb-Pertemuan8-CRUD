<?php
declare(strict_types=1);

/**
 * Koneksi PDO dengan Singleton pattern.
 * Kredensial dibaca dari file .env (tidak di-hardcode).
 */
final class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $env = self::loadEnv(__DIR__ . '/../.env');

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $env['DB_HOST'] ?? 'localhost',
            $env['DB_NAME'] ?? 'inventaris_db'
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $env['DB_USER'] ?? 'root', $env['DB_PASS'] ?? '', $options);
        } catch (PDOException $e) {
            error_log('DB Connection Error: ' . $e->getMessage());
            http_response_code(500);
            exit('Terjadi kesalahan koneksi database.');
        }
    }

    private function __clone() {}

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    private static function loadEnv(string $file): array
    {
        $vars = [];
        if (!is_readable($file)) {
            return $vars;
        }
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if ($line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $vars[trim($k)] = trim($v);
        }
        return $vars;
    }
}
