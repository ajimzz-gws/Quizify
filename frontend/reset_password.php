<?php
require_once 'bootstrap.php';

$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $passwordRepeat = $_POST['password_repeat'];
    
    if ($password !== $passwordRepeat) {
        header("Location: reset_password.php?token=$token&error=Passwords don't match");
        exit;
    }
    
    // Verify token
    $stmt = $db->pdo->prepare(
        "SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()"
    );
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $db->pdo->prepare(
            "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?"
        )->execute([$hashed, $user['id']]);
        
        header("Location: login.php?success=Password updated successfully");
        exit;
    }
    
    header("Location: login.php?error=Invalid or expired token");
    exit;
}

// Verify token before showing form
$stmt = $db->pdo->prepare(
    "SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()"
);
$stmt->execute([$token]);
$valid = $stmt->fetch();

if (!$valid) {
    header("Location: login.php?error=Invalid or expired token");
    exit;
}

include '../templates/header.php';
?>

<div class="wrapper">
    <form method="post">
        <h1>Reset Password</h1>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        
        <div class="input-box">
            <input type="password" name="password" placeholder="New password (min 8 chars)" required />
        </div>
        
        <div class="input-box">
            <input type="password" name="password_repeat" placeholder="Repeat new password" required />
        </div>
        
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>" />
        <button type="submit" class="btn">Reset Password</button>
    </form>
</div>

<?php include '../templates/footer.php'; ?>