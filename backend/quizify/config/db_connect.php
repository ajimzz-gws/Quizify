<?php
// db_connect.php
$host = 'localhost';
$user = 'root';          // change if needed
$pass = '';              // change if needed
$db   = 'quizify';    // change if needed

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>