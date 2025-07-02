<?php
require 'db_connect.php';

$fullName = "Mr. Ahmad Fahmi";
$email = "teacher@example.com";
$password = password_hash("securepassword", PASSWORD_BCRYPT);
$role = "teacher";

$stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $fullName, $email, $password, $role);
$stmt->execute();

echo "Teacher inserted with ID: " . $conn->insert_id;