<?php
require_once __DIR__ . '/../config/db_connect.php';

$teacherId = $_POST['teacher_id']; // from frontend form
$amount = $_POST['amount'] ?? 5;
$category = $_POST['category'] ?? null;
$difficulty = $_POST['difficulty'] ?? null;

$url = "https://opentdb.com/api.php?amount=$amount&type=multiple";
if ($category) $url .= "&category=$category";
if ($difficulty) $url .= "&difficulty=$difficulty";

$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data['response_code'] !== 0) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to fetch questions."]);
    exit;
}

$openQuestions = $data['results'];
$converted = [];

foreach ($openQuestions as $q) {
    $options = $q['incorrect_answers'];
    $correctAnswer = $q['correct_answer'];
    $correctIndex = rand(0, count($options)); // randomize correct position
    array_splice($options, $correctIndex, 0, $correctAnswer);

    $converted[] = [
        "questionText" => html_entity_decode($q['question']),
        "questionType" => "mcq",
        "options" => array_map("html_entity_decode", $options),
        "correct" => $correctIndex,
        "score" => 1
    ];
}

$title = "Imported Quiz from OpenTDB";
$desc = "Auto-generated quiz via OpenTDB";
$category = $category ?? "General";
$timeLimit = 300;
$isRandomized = true;
$questionsJson = json_encode($converted);

$stmt = $conn->prepare("INSERT INTO quizzes (title, description, created_by, category, time_limit, is_randomized, questions_json) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssiisis", $title, $desc, $teacherId, $category, $timeLimit, $isRandomized, $questionsJson);
$stmt->execute();

echo json_encode(["message" => "Quiz imported!", "quiz_id" => $conn->insert_id]);