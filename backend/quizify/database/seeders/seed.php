<?php
$host = 'localhost';
$user = 'root'; // Your DB username
$pass = 'afas2004';     // Your DB password
$db = 'quizify';  // Your DB name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully!<br>";

// Seed Users
$users = [
    ['full_name' => 'Alice Student', 'email' => 'alice@student.com', 'password' => 'password123', 'role' => 'student'],
    ['full_name' => 'Bob Student', 'email' => 'bob@student.com', 'password' => 'password123', 'role' => 'student'],
    ['full_name' => 'Mr. Smith', 'email' => 'smith@teacher.com', 'password' => 'password123', 'role' => 'teacher'],
];

// Insert users
$user_ids = [];
foreach ($users as $user) {
    $password = password_hash($user['password'], PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (full_name, email, password, role) VALUES 
           ('{$user['full_name']}', '{$user['email']}', '$password', '{$user['role']}')";
    if ($conn->query($sql) === TRUE) {
        $user_ids[$user['email']] = $conn->insert_id;
        echo "Seeded user: {$user['full_name']}<br>";
    } else {
        echo "Error seeding user: " . $conn->error . "<br>";
    }
}

// Seed Categories
$categories = [
    ['name' => 'Math', 'description' => 'Mathematics quizzes'],
    ['name' => 'Science', 'description' => 'Science quizzes'],
];

$category_ids = [];
foreach ($categories as $cat) {
    $sql = "INSERT INTO categories (name, description) VALUES 
           ('{$cat['name']}', '{$cat['description']}')";
    if ($conn->query($sql) === TRUE) {
        $category_ids[$cat['name']] = $conn->insert_id;
        echo "Seeded category: {$cat['name']}<br>";
    } else {
        echo "Error seeding category: " . $conn->error . "<br>";
    }
}

// Seed Quizzes
$quizzes = [
    [
        'title' => 'Basic Math Quiz',
        'description' => 'Test your basic math skills.',
        'created_by' => $user_ids['smith@teacher.com'],
        'category' => 'Math',
        'time_limit' => 30,
        'is_randomized' => 1,
        'questions_json' => json_encode(['Q1' => '2+2?', 'Q2' => '3x3?']),
    ],
];

$quiz_ids = [];
foreach ($quizzes as $quiz) {
    $sql = "INSERT INTO quizzes (title, description, created_by, category, time_limit, is_randomized, questions_json) VALUES 
           ('{$quiz['title']}', '{$quiz['description']}', {$quiz['created_by']}, '{$quiz['category']}', {$quiz['time_limit']}, {$quiz['is_randomized']}, '{$conn->real_escape_string($quiz['questions_json'])}')";
    if ($conn->query($sql) === TRUE) {
        $quiz_ids[] = $conn->insert_id;
        echo "Seeded quiz: {$quiz['title']}<br>";
    } else {
        echo "Error seeding quiz: " . $conn->error . "<br>";
    }
}

// Seed Quiz Attempts
$attempts = [
    [
        'user_id' => $user_ids['alice@student.com'],
        'quiz_id' => $quiz_ids[0],
        'score' => 85,
        'answers_json' => json_encode(['Q1' => '4', 'Q2' => '9']),
    ],
];

foreach ($attempts as $attempt) {
    $sql = "INSERT INTO quiz_attempts (user_id, quiz_id, score, answers_json) VALUES 
           ({$attempt['user_id']}, {$attempt['quiz_id']}, {$attempt['score']}, '{$conn->real_escape_string($attempt['answers_json'])}')";
    if ($conn->query($sql) === TRUE) {
        echo "Seeded quiz attempt for user ID {$attempt['user_id']}<br>";
    } else {
        echo "Error seeding quiz attempt: " . $conn->error . "<br>";
    }
}

// Seed Feedback
$feedbacks = [
    [
        'quiz_id' => $quiz_ids[0],
        'user_id' => $user_ids['alice@student.com'],
        'comment' => 'Nice quiz!',
        'rating' => 4,
    ],
];

foreach ($feedbacks as $fb) {
    $sql = "INSERT INTO feedback (quiz_id, user_id, comment, rating) VALUES 
           ({$fb['quiz_id']}, {$fb['user_id']}, '{$conn->real_escape_string($fb['comment'])}', {$fb['rating']})";
    if ($conn->query($sql) === TRUE) {
        echo "Seeded feedback for quiz ID {$fb['quiz_id']}<br>";
    } else {
        echo "Error seeding feedback: " . $conn->error . "<br>";
    }
}

$conn->close();
?>
