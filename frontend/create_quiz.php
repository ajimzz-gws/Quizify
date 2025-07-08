<?php
require_once '../app/bootstrap.php';
$auth->requireRole('teacher');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => $_POST['title'],
        'description' => $_POST['description'] ?? null,
        'created_by' => $_SESSION['user_id'],
        'questions_json' => json_encode($_POST['questions']),
        'status' => $_POST['action'] === 'publish' ? 'published' : 'draft'
    ];

    $quizId = $db->insert('quizzes', $data);
    header("Location: quiz_preview.php?id=$quizId");
    exit;
}

// Display form
include '../templates/header.php';
?>
<form method="post" id="quiz-form">
    <input type="text" name="title" placeholder="Quiz Title" required>
    <textarea name="description" placeholder="Description"></textarea>
    
    <div id="questions-container">
        <!-- Questions will be added here via JavaScript -->
    </div>
    
    <button type="button" id="add-question">Add Question</button>
    <button type="submit" name="action" value="save">Save Draft</button>
    <button type="submit" name="action" value="publish">Publish</button>
</form>

<script>
// Keep your existing JavaScript for dynamic question adding
// but modify AJAX calls to submit to this same PHP file
</script>

<?php include '../templates/footer.php'; ?>