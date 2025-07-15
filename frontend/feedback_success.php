<?php
require_once 'bootstrap.php';
$auth->requireRole('teacher');

$quizId = $_GET['quiz_id'] ?? 0;
$userId = $_GET['user_id'] ?? 0;

// Get the latest attempt and feedback
$attempt = $db->pdo->query("
    SELECT qa.*, q.title, u.full_name as student_name
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    JOIN users u ON qa.user_id = u.id
    WHERE qa.quiz_id = $quizId AND qa.user_id = $userId
    ORDER BY qa.completed_at DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

$feedback = $db->pdo->query("
    SELECT * FROM feedback 
    WHERE quiz_id = $quizId AND user_id = $userId
    ORDER BY created_at DESC 
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feedback Submitted</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow text-center">
            <!-- Success icon and heading -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-green-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Feedback Submitted Successfully!</h1>
            
            <!-- Feedback details -->
            <div class="mb-6 text-left bg-blue-50 p-4 rounded-lg">
                <p class="font-semibold">Student: <?= htmlspecialchars($attempt['student_name']) ?></p>
                <p>Quiz: <?= htmlspecialchars($attempt['title']) ?></p>
                <p class="mt-2">
                    <span class="font-semibold">Rating:</span>
                    <span class="text-yellow-500 ml-2">
                        <?= str_repeat('★', $feedback['rating']) . str_repeat('☆', 5 - $feedback['rating']) ?>
                    </span>
                </p>
                <?php if (!empty($feedback['comment'])): ?>
                    <p class="mt-2"><span class="font-semibold">Comments:</span></p>
                    <p class="bg-white p-2 rounded"><?= htmlspecialchars($feedback['comment']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Action buttons -->
            <div class="flex justify-center gap-4">
                <a href="feedback.php?attempt_id=<?= $attempt['id'] ?>" 
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Edit Feedback
                </a>
                <a href="view_attempts.php?id=<?= $attempt['quiz_id'] ?>" 
                class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    View Attempts
                </a>
            </div>
        </div>
    </div>
</body>
</html>