<?php
//get_all_quizzes.php
require_once __DIR__ . '/../config/db_connect.php';

$result = $conn->query("SELECT id, title, description FROM quizzes");
$quizzes = [];

while ($row = $result->fetch_assoc()) {
    $quizzes[] = $row;
}

header('Content-Type: application/json');
echo json_encode($quizzes);