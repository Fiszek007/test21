<?php
include 'db_connection.php';
session_start();

header('Content-Type: application/json');

// Ensure admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User ID not provided.']);
    exit;
}

$user_id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    echo json_encode(['status' => 'success', 'message' => 'User deleted successfully.']);
} catch (PDOException $e) {
    error_log("Error deleting user: " . $e->getMessage(), 3, "error_log.txt");
    echo json_encode(['status' => 'error', 'message' => 'An error occurred while deleting the user.']);
}
?>
