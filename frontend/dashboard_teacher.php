<?php
require_once '../app/bootstrap.php';
$auth->requireRole('teacher');

// Get teacher's quizzes and stats
$userId = $_SESSION['user_id'];
$quizzes = $db->pdo->query("
    SELECT q.*, 
           (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.id) as attempt_count,
           (SELECT AVG(score) FROM quiz_attempts WHERE quiz_id = q.id) as avg_score
    FROM quizzes q
    WHERE q.created_by = $userId
    ORDER BY q.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

include '../templates/header.php';
?>

<h1>Teacher Dashboard</h1>

<div class="teacher-stats">
    <div class="stat-card">
        <h3><?= count($quizzes) ?></h3>
        <p>Quizzes Created</p>
    </div>
    <div class="stat-card">
        <h3><?= array_sum(array_column($quizzes, 'attempt_count')) ?></h3>
        <p>Total Attempts</p>
    </div>
</div>

<h2>Your Quizzes</h2>
<table class="quiz-table">
    <thead>
        <tr>
            <th>Title</th>
            <th>Questions</th>
            <th>Attempts</th>
            <th>Avg. Score</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($quizzes as $quiz): ?>
        <tr>
            <td><?= htmlspecialchars($quiz['title']) ?></td>
            <td><?= count(json_decode($quiz['questions_json'])) ?></td>
            <td><?= $quiz['attempt_count'] ?></td>
            <td><?= round($quiz['avg_score'], 1) ?></td>
            <td>
                <a href="view_attempts.php?quiz_id=<?= $quiz['id'] ?>">View Attempts</a>
                <a href="edit_quiz.php?id=<?= $quiz['id'] ?>">Edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="create_quiz.php" class="btn">Create New Quiz</a>

<?php include '../templates/footer.php'; ?>