<?php
// config.php
// Update these values for your MySQL server
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_NAME = getenv('DB_NAME') ?: 'catastro';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: 'root';

try {
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (PDOException $e) {
  http_response_code(500);
  echo "Database connection error: " . htmlspecialchars($e->getMessage());
  exit;
}

function h($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
?>
