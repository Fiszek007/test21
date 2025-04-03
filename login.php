<?php
include 'db_connection.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);

    if (empty($login) || empty($password)) {
        $error = "Both fields are required.";
    } else {
        $sql = "SELECT * FROM users WHERE login = :login";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['login' => $login]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['password'] === $password) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'Admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: welcome.php");
                }
                exit();
            } else {
                $error = "Invalid login or password.";
            }
        } else {
            $error = "Invalid login or password.";
        }
    }

    if (!empty($error)) {
        header("Location: login.html?error=" . urlencode($error));
        exit();
    }
}
?>
