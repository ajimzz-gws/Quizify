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

// Handle quiz duplication
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['duplicate_quiz']) && $_SESSION['user_role'] === 'teacher') {
    $quizId = $_POST['quiz_id'];
    
    // Get the original quiz
    $stmt = $db->pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
    $stmt->execute([$quizId]);
    $originalQuiz = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($originalQuiz) {
        // Create a duplicated quiz
        $newTitle = "Copy of " . $originalQuiz['title'];
        $newSlug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $newTitle)) . '-' . time();
        
        $insertStmt = $db->pdo->prepare("
            INSERT INTO quizzes 
            (title, description, questions_json, created_by, status, created_at)
            VALUES (?, ?, ?, ?, 'draft', NOW())
        ");
        
        $insertStmt->execute([
            $newTitle,
            $originalQuiz['description'],
            $originalQuiz['questions_json'],
            $_SESSION['user_id']
        ]);
        
        $newQuizId = $db->pdo->lastInsertId();
        header("Location: edit_quiz.php?id=$newQuizId");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quiz Library</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    function confirmDuplicate(quizTitle) {
      return confirm(`Are you sure you want to duplicate "${quizTitle}"?`);
    }
  </script>
</head>
<body class="bg-blue-50">
  <header class="bg-blue-700 text-white py-5 shadow-md">
    <div class="container mx-auto px-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold">Quiz Library</h1>
      <a href="dashboard_teacher.php" class="flex items-center text-white hover:text-blue-200 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Back to Dashboard
      </a>
    </div>
  </header>

  <main class="container mx-auto px-4 py-8">
    <?php if (isset($_GET['duplicated']) && $_GET['duplicated'] === 'success'): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        Quiz duplicated successfully! You can now edit your copy.
      </div>
    <?php endif; ?>

    <?php if (empty($quizzes)): ?>
      <div class="text-center py-12 text-gray-600">
        <p class="text-lg">No quizzes available at the moment.</p>
        <a href="dashboard_teacher.php" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
          Return to Dashboard
        </a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($quizzes as $quiz): 
          $questionCount = count(json_decode($quiz['questions_json']));
        ?>
          <div class="bg-white rounded-lg shadow-md overflow-hidden border-l-4 border-blue-600 hover:shadow-lg transition-shadow duration-200 hover:-translate-y-1">
            <div class="p-6">
              <h3 class="text-xl font-semibold text-blue-800 mb-3"><?= htmlspecialchars($quiz['title']) ?></h3>
              <p class="text-gray-600 mb-4"><?= htmlspecialchars($quiz['description']) ?></p>
              <p class="text-sm text-gray-500 mb-4">
                By <?= htmlspecialchars($quiz['creator_name']) ?> • 
                <?= $questionCount ?> question<?= $questionCount !== 1 ? 's' : '' ?>
              </p>
              
              <div class="flex flex-wrap gap-2">
                <?php if ($_SESSION['user_role'] === 'teacher'): ?>
                  <a href="edit_quiz.php?id=<?= $quiz['id'] ?>" 
                     class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-sm font-medium text-center">
                    Edit
                  </a>
                  
                  <form method="POST" onsubmit="return confirmDuplicate('<?= addslashes($quiz['title']) ?>')" class="w-full">
                    <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
                    <button type="submit" name="duplicate_quiz" 
                            class="w-full px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors text-sm font-medium">
                      Duplicate
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>