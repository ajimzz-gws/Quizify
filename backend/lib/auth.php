<?php
// lib/auth.php
function require_login() {
  session_start();
  if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
  }
}