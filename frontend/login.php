<?php
require_once '../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    $stmt = $db->pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['full_name'];

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 30 * 24 * 60 * 60); // 30 days
            
            $db->pdo->prepare(
                "UPDATE users SET remember_token = ?, remember_expires = ? WHERE id = ?"
            )->execute([$token, $expires, $user['id']]);

            setcookie('remember', $token, time() + 30 * 24 * 60 * 60, '/', '', true, true);
        }

        header("Location: " . ($user['role'] === 'teacher' ? 'dashboard_teacher.php' : 'dashboard_student.php'));
        exit;
    }

    header("Location: login.php?error=Invalid email or password");
    exit;
}

header("Location: login.php");