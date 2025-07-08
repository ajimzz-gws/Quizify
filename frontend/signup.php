<?php
require_once 'bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $passwordRepeat = $_POST['psw-repeat'];
    $role = $_POST['role'] ?? 'student';

    // Validation
    if ($password !== $passwordRepeat) {
        header("Location: signup.php?error=Passwords don't match");
        exit;
    }

    if (strlen($password) < 8) {
        header("Location: signup.php?error=Password must be at least 8 characters");
        exit;
    }

    // Check if email exists
    $stmt = $db->pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        header("Location: signup.php?error=Email already exists");
        exit;
    }

    // Create user
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->pdo->prepare(
        "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$name, $email, $hashed, $role]);

    // Auto-login
    $_SESSION['user_id'] = $db->pdo->lastInsertId();
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = $role;
    $_SESSION['user_name'] = $name;

    header("Location: " . ($role === 'teacher' ? 'dashboard_teacher.php' : 'dashboard_student.php'));
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - Quizify</title>
    <link rel="stylesheet" href="signup1.css"> <!-- Adjust path as needed -->
</head>
<body>

<div class="wrapper">
    <form method="post">
        <h1>Sign Up</h1>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <div class="input-box">
            <label for="name"><b>Full Name</b></label>
            <input type="text" name="name" placeholder="Enter your name" required />
        </div>

        <div class="input-box">
            <label for="email"><b>Email</b></label>
            <input type="email" name="email" placeholder="Enter email" required />
        </div>

        <div class="input-box">
            <label for="psw"><b>Password</b></label>
            <input type="password" name="password" placeholder="Enter password (min 8 chars)" required />
        </div>

        <div class="input-box">
            <label for="psw-repeat"><b>Repeat password</b></label>
            <input type="password" name="psw-repeat" placeholder="Repeat password" required />
        </div>

        <div class="input-box">
            <label for="role"><b>Account Type</b></label>
            <select name="role" required>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
            </select>
        </div>

        <button type="submit" class="signup">Sign Up</button>
        <a href="login.php" class="btn">Already have an account? Login</a>
    </form>
</div>

