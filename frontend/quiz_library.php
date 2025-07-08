<?php
require_once 'bootstrap.php';
$auth->requireLogin();

// Get all published quizzes
$quizzes = $db->pdo->query("
    SELECT q.*, u.full_name as creator_name 
    FROM quizzes q
    JOIN users u ON q.created_by = u.id
    WHERE q.status = 'published'
    ORDER BY q.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

include '../templates/header.php';
?>

<h1>Quiz Library</h1>

<div class="quiz-grid">
    <?php foreach ($quizzes as $quiz): ?>
    <div class="quiz-card">
        <h3><?= htmlspecialchars($quiz['title']) ?></h3>
        <p class="description"><?= htmlspecialchars($quiz['description']) ?></p>
        <p class="meta">By <?= htmlspecialchars($quiz['creator_name']) ?> • 
           <?= count(json_decode($quiz['questions_json'])) ?> questions</p>
        
        <div class="actions">
            <a href="take_quiz.php?id=<?= $quiz['id'] ?>" class="btn">Take Quiz</a>
            <?php if ($_SESSION['user_role'] === 'teacher'): ?>
                <a href="edit_quiz.php?id=<?= $quiz['id'] ?>" class="btn">Edit</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include '../templates/footer.php'; ?>