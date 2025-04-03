<?php
include 'db_connection.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

// Get user data from session
$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];

// Read the input from the POST request
$data = json_decode(file_get_contents('php://input'), true);
$subject = trim($data['subject'] ?? '');
$message = trim($data['message'] ?? '');

// Validate required fields
if (empty($subject) || empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Subject and message are required.']);
    exit();
}

try {
    // Insert the message into the database
    $stmt = $pdo->prepare("
        INSERT INTO messages (user_id, email, subject, message)
        VALUES (:user_id, :email, :subject, :message)
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':email' => $email,
        ':subject' => $subject,
        ':message' => $message
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Message sent successfully.']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
