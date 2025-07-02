<?php
//submit_attempt.php
require 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$userId = $data['user_id'];
$quizId = $data['quiz_id'];
$answersJson = json_encode($data['answers']);
$score = $data['score'];

$stmt = $conn->prepare("INSERT INTO quiz_attempts (user_id, quiz_id, score, answers_json) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiis", $userId, $quizId, $score, $answersJson);
$stmt->execute();

echo json_encode(["message" => "Attempt recorded"]);