<?php
// api/login.php
require_once __DIR__ . '/../config/db_connect.php';

session_start();
$email    = $_POST['email']    ?? '';
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

$stmt = $conn->prepare("SELECT id, password, role, full_name FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
  // Successful login
  $_SESSION['user_id']   = $user['id'];
  $_SESSION['user_name'] = $user['full_name'];
  $_SESSION['role']      = $user['role'];

  // Remember me: set cookie for 7 days
  if ($remember) {
    setcookie('user_id', $user['id'], time()+604800, '/');
  }

  header('Location: ../dashboard.php');
  exit;
} else {
  // Invalid credentials
  header('Location: ../public/login.php?error=1');
  exit;
}