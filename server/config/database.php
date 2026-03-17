<?php
/**
 * database.php  (config)
 * Returns a singleton PDO connection using environment variables.
 */

require_once __DIR__ . '/../utils/helpers.php';

// Ensure environment values are available even when this file is hit directly.
loadEnv(dirname(__DIR__, 2) . '/.env');

function envFirst(array $keys, string $default = ''): string
{
    foreach ($keys as $key) {
        $value = $_ENV[$key] ?? getenv($key) ?? '';
        if ($value !== '') {
            return (string) $value;
        }
    }
    return $default;
}

function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host   = envFirst(['DB_HOST', 'MYSQL_HOST'], '127.0.0.1');
        $dbname = envFirst(['DB_NAME', 'DB_DATABASE', 'MYSQL_DATABASE'], 'glodblive');
        $user   = envFirst(['DB_USER', 'DB_USERNAME', 'MYSQL_USER'], 'glodblive');
        $pass   = envFirst(['DB_PASSWORD', 'DB_PASS', 'MYSQL_PASSWORD', 'MYSQL_PASS'], '');
        $port   = envFirst(['DB_PORT', 'MYSQL_PORT'], '3306');

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    return $pdo;
}
