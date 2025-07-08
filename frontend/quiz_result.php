<?php
require_once '../app/bootstrap.php';
$auth->requireLogin();

$attemptId = $_GET['attempt_id'] ?? null;
$attempt = $db->get('quiz_attempts', $attemptId);

if (!$attempt || $attempt['user_id'] !== $_SESSION['user_id']) {
    header("Location: access_denied.php");
    exit;
}

$quiz = $db->get('quizzes', $attempt['quiz_id']);
$questions = json_decode($quiz['questions_json'], true);
$userAnswers = json_decode($attempt['answers_json'], true);
$percentage = round(($attempt['score'] / count($questions)) * 100);

// Determine feedback message
if ($percentage == 100) {
    $feedback = "🌟 Perfect! You've mastered this quiz.";
} elseif ($percentage >= 80) {
    $feedback = "🎉 Great job! You have a solid understanding.";
} elseif ($percentage >= 50) {
    $feedback = "👍 Good attempt! Consider reviewing a few topics.";
} else {
    $feedback = "💡 Keep practicing. You'll get better!";
}
?>

<h1>Quiz Results</h1>
<p>You scored <?= $attempt['score'] ?> out of <?= count($questions) ?> (<?= $percentage ?>%)</p>
<p><?= $feedback ?></p>

<h2>Question Review</h2>
<?php foreach ($questions as $q): ?>
<div class="question-review">
    <h3><?= htmlspecialchars($q['question']) ?></h3>
    <p>Your answer: <?= htmlspecialchars($userAnswers[$q['id']] ?? 'Not answered') ?></p>
    <p>Correct answer: <?= htmlspecialchars($q['correct_answer']) ?></p>
</div>
<?php endforeach; ?>

<a href="quiz_library.php" class="btn">Back to Quiz Library</a>