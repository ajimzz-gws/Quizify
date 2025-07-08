<?php
require_once '../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    $stmt = $db->pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
        
        $db->pdo->prepare(
            "UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?"
        )->execute([$token, $expires, $user['id']]);
        
        // Send email with reset link (implementation depends on your mail setup)
        $resetLink = "http://yourdomain.com/reset_password.php?token=$token";
        // mail($email, "Password Reset", "Click here to reset: $resetLink");
    }
    
    // Always show success message to prevent email enumeration
    header("Location: forgot_password.php?success=1");
    exit;
}

include '../templates/header.php';
?>

<div class="wrapper">
    <form method="post">
        <h1>Forgot Password</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success">If an account exists, we've sent a reset link</div>
        <?php endif; ?>
        
        <div class="input-box">
            <input type="email" name="email" placeholder="Enter your email" required />
        </div>
        
        <button type="submit" class="btn">Send Reset Link</button>
        <a href="login.php" class="btn">Back to Login</a>
    </form>
</div>

<?php include '../templates/footer.php'; ?>