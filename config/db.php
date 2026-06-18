<?php
$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
$db   = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'corner_flag_arena';
$user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Log the real error to a file, show a generic message to the user
     $log_dir = __DIR__ . '/../logs';
     if (!is_dir($log_dir)) {
         @mkdir($log_dir, 0700, true);
     }
     @file_put_contents(
         $log_dir . '/db_errors.log',
         '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . PHP_EOL,
         FILE_APPEND
     );
     http_response_code(503);
     die('Service temporarily unavailable. Please try again later.');
}
?>
