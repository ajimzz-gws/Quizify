<?php
require_once 'bootstrap.php';

$auth->requireRole('teacher');

$attemptId = $_GET['attempt'] ?? 0;

// Fetch attempt details
$attempt = $db->pdo->query("
    SELECT qa.*, q.title, u.full_name 
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    JOIN users u ON qa.user_id = u.id
    WHERE qa.id = $attemptId
")->fetch(PDO::FETCH_ASSOC);

if (!$attempt) {
    $_SESSION['error'] = "Attempt not found";
    header("Location: reports.php");
    exit;
}

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = trim($_POST['comment'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);
    
    // Validate rating (1-5)
    if ($rating < 1 || $rating > 5) {
        $_SESSION['error'] = "Please select a valid rating (1-5)";
    } else {
        // Check if feedback exists
        $existing = $db->pdo->query("SELECT id FROM feedback WHERE quiz_id = {$attempt['quiz_id']} AND user_id = {$attempt['user_id']}")->fetch();
        
        if ($existing) {
            // Update existing feedback
            $stmt = $db->pdo->prepare("UPDATE feedback SET comment = ?, rating = ?, created_at = NOW() WHERE id = ?");
            $stmt->execute([$comment, $rating, $existing['id']]);
        } else {
            // Insert new feedback
            $stmt = $db->pdo->prepare("INSERT INTO feedback (quiz_id, user_id, comment, rating) VALUES (?, ?, ?, ?)");
            $stmt->execute([$attempt['quiz_id'], $attempt['user_id'], $comment, $rating]);
        }
        
        // Redirect to success page with identifiers
        header("Location: feedback_success.php?quiz_id={$attempt['quiz_id']}&user_id={$attempt['user_id']}");
        exit;
    }
}

// Get existing feedback if any
$feedback = $db->pdo->query("
    SELECT * FROM feedback 
    WHERE quiz_id = {$attempt['quiz_id']} AND user_id = {$attempt['user_id']}
")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Provide Feedback</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Provide Feedback</h1>
            
            <div class="mb-6">
                <p class="font-semibold">Student: <?= htmlspecialchars($attempt['full_name']) ?></p>
                <p>Quiz: <?= htmlspecialchars($attempt['title']) ?></p>
                <p>Score: <?= $attempt['score'] ?>%</p>
            </div>

            <form method="POST">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Rating</label>
                    <div class="flex space-x-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="rating" value="<?= $i ?>" 
                                    <?= isset($feedback['rating']) && $feedback['rating'] == $i ? 'checked' : '' ?> 
                                    class="hidden peer">
                                <span class="text-2xl peer-checked:text-yellow-500"><?= $i >= 4 ? '★' : ($i >= 3 ? '★' : '★') ?></span>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="comment" class="block text-gray-700 mb-2">Comments</label>
                    <textarea id="comment" name="comment" rows="4" 
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($feedback['comment'] ?? '') ?></textarea>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="view_attempts.php?id=<?= $attempt['quiz_id'] ?>" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Submit Feedback</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>