<?php
$host = '0.0.0.0';
$port = '3306';
$dbname = 'avia_tickets';
$username = 'root';
$password = '';

try {
    $dsn = 'mysql:host=$host;port=$port;dbname=$dbname';
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . htmlspecialchars($e->getMessage()));
}
?>