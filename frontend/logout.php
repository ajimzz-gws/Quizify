<?php
require_once 'bootstrap.php';

// Clear session
session_unset();
session_destroy();

// Clear remember cookie
if (isset($_COOKIE['remember'])) {
    setcookie('remember', '', time() - 3600, '/');
}

header("Location: login.html");
exit;