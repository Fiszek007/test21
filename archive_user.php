<?php
include 'db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['delete_user_id']) && !empty($data['delete_user_id'])) {
    $user_id = $data['delete_user_id'];

    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Fetch user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Insert user data into archive_users
            $stmt = $pdo->prepare("INSERT INTO users_archive (user_id, login, first_name, last_name, email, phone_number, password, role, archived_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$user['user_id'], $user['login'], $user['first_name'], $user['last_name'], $user['email'], $user['phone_number'], $user['password'], $user['role']]);
            
            // Delete user from users table
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);

            // Commit transaction
            $pdo->commit();

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found.']);
        }
    } catch (PDOException $e) {
        // Rollback transaction
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => htmlspecialchars($e->getMessage())]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'User ID not provided or empty.']);
}

// Fetch all data from archive_users table
try {
    $stmt = $pdo->query("SELECT * FROM users_archive");
    $archivedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $archivedUsers]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => htmlspecialchars($e->getMessage())]);
}
?>
