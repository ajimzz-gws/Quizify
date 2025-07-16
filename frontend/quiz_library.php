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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quiz Library</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .truncate-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;       /* max 2 lines */
      -webkit-box-orient: vertical;
      overflow: hidden;
      text-overflow: ellipsis;
      height: 3em;
    }
  </style>
</head>
<body class="bg-blue-50">
  <header class="bg-blue-700 text-white py-5 shadow-md">
    <div class="container mx-auto px-4">
      <h1 class="text-2xl font-bold">Quiz Library</h1>
    </div>
  </header>

  <main class="container mx-auto px-4 py-8">
    <div class="fixed bottom-4 left-4 z-50">
      <a href="dashboard_student.php" 
        class="flex items-center px-4 py-3 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition-colors">
        <i class="fas fa-home mr-2"></i> Dashboard
      </a>
  </div>
    <?php if (empty($quizzes)): ?>
      <div class="text-center py-12 text-gray-600">
        <p class="text-lg">No quizzes available at the moment.</p>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($quizzes as $quiz): 
          $questionCount = count(json_decode($quiz['questions_json']));
        ?>
          <div class="bg-white rounded-lg shadow-md overflow-hidden border-l-4 border-blue-600 hover:shadow-lg transition-shadow duration-200 hover:-translate-y-1">
            <div class="p-6">
              <h3 class="text-xl font-semibold text-blue-800 mb-3"><?= htmlspecialchars($quiz['title']) ?></h3>
              <p class="text-gray-600 mb-4 truncate-2"><?= htmlspecialchars($quiz['description']) ?></p>
              <p class="text-sm text-gray-500 mb-4">
                By <?= htmlspecialchars($quiz['creator_name']) ?> • 
                <?= $questionCount ?> question<?= $questionCount !== 1 ? 's' : '' ?>
              </p>
              
              <div class="flex flex-wrap gap-2">
                <a href="take_quiz.php?id=<?= $quiz['id'] ?>" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm font-medium">
                  Take Quiz
                </a>
                <?php if ($_SESSION['user_role'] === 'teacher'): ?>
                  <a href="edit_quiz.php?id=<?= $quiz['id'] ?>" 
                     class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors text-sm font-medium">
                    Edit
                  </a>
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