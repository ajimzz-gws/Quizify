<?php
// seed_quiz.php
require 'db_connect.php';

$teacherId = 1;
$quizTitle = "General Knowledge Quiz";
$description = "A quick test of world facts.";
$category = "General";
$timeLimit = 300;
$isRandomized = true;

$questions = [
    [
        "questionText" => "What is the capital of France?",
        "questionType" => "mcq",
        "options" => ["Paris", "London", "Berlin", "Madrid"],
        "correct" => 0,
        "score" => 1
    ],
    [
        "questionText" => "The Earth is flat.",
        "questionType" => "true_false",
        "correct" => false,
        "score" => 1
    ]
];

$jsonQuestions = json_encode($questions);

$stmt = $conn->prepare("INSERT INTO quizzes (title, description, created_by, category, time_limit, is_randomized, questions_json) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssiisis", $quizTitle, $description, $teacherId, $category, $timeLimit, $isRandomized, $jsonQuestions);
$stmt->execute();

echo "Quiz seeded successfully!";
