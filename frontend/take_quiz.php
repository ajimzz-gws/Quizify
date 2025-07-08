<?php
require_once '../app/bootstrap.php';
$auth->requireLogin();

$quizId = $_GET['id'] ?? null;
$quiz = $db->get('quizzes', $quizId);

if (!$quiz) {
    header("Location: quiz_not_found.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = calculateScore($_POST['answers'], json_decode($quiz['questions_json'], true));
    
    $attemptData = [
        'user_id' => $_SESSION['user_id'],
        'quiz_id' => $quizId,
        'score' => $score,
        'answers_json' => json_encode($_POST['answers'])
    ];
    
    $db->insert('quiz_attempts', $attemptData);
    header("Location: quiz_result.php?attempt_id=" . $db->lastInsertId());
    exit;
}

function calculateScore($userAnswers, $questions) {
    $correct = 0;
    foreach ($questions as $q) {
        if ($userAnswers[$q['id']] === $q['correct_answer']) {
            $correct++;
        }
    }
    return $correct;
}
?>

<!-- Quiz Display -->
<h1><?= htmlspecialchars($quiz['title']) ?></h1>
<p><?= htmlspecialchars($quiz['description']) ?></p>

<form method="post">
    <?php foreach (json_decode($quiz['questions_json'], true) as $i => $q): ?>
    <div class="question">
        <h3><?= ($i+1) ?>. <?= htmlspecialchars($q['question']) ?></h3>
        <?php foreach ($q['choices'] as $choice): ?>
        <label>
            <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= htmlspecialchars($choice) ?>">
            <?= htmlspecialchars($choice) ?>
        </label>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    <button type="submit">Submit Quiz</button>
</form>