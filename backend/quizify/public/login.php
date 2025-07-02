<!-- public/login.php -->
<?php
  session_start();
  if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
  }
?>
<!DOCTYPE html>
<html lang="en">
<head> … </head>
<body>
  <div class="wrapper">
    <form action="../api/login.php" method="POST">

      <h1>Log In</h1>

      <div class="input-box">
        <label>Username</label>
        <input type="text" name="email" placeholder="Email" required>
      </div>

      <div class="input-box">
        <label>Password</label>
        <input type="password" name="password" placeholder="Password" required>
      </div>

      <div class="remember-forgot">
        <label><input type="checkbox" name="remember">Remember me</label>
        <a href="#forgot-password">Forgot Password</a>
      </div>

      <button type="submit" class="btn">Login</button>
      <div class="register-link">
        <p>Don't have an account? <a href="register.php">Register</a></p>
      </div>

    </form>
  </div>
</body>
</html>