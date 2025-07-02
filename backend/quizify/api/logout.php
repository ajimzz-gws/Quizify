<?php
// api/logout.php
session_start();
session_unset();
session_destroy();
setcookie('user_id', '', time()-3600, '/');
header('Location: ../public/login.php');
exit;