<?php
include 'db_connection.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validate required fields
    if (empty($login) || empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    try {
        // Check if the login or email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE login = :login OR email = :email");
        $stmt->execute([':login' => $login, ':email' => $email]);
        $existingCount = $stmt->fetchColumn();

        if ($existingCount > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Login or email already exists.']);
            exit;
        }

        // Insert the new user into the database
        $role = 'User'; // Default role for new users
        $stmt = $pdo->prepare("
            INSERT INTO users (login, first_name, last_name, email, phone_number, password, role)
            VALUES (:login, :first_name, :last_name, :email, :phone_number, :password, :role)
        ");
        $stmt->execute([
            ':login' => $login,
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':email' => $email,
            ':phone_number' => $phone_number,
            ':password' => $hashedPassword,
            ':role' => $role
        ]);

        echo json_encode(['status' => 'success', 'message' => 'User registered successfully.']);
    } catch (PDOException $e) {
        // Log the error
        error_log("Error during registration: " . $e->getMessage(), 3, 'error_log.txt');
        echo json_encode(['status' => 'error', 'message' => 'An error occurred during registration.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
header("Location: index.html");
?>
