<?php
require_once 'bootstrap.php';
$auth->requireRole('teacher');

$userId = $_SESSION['user_id'];
$teacher = $db->pdo->query("
    SELECT u.*, 
           COUNT(q.id) as total_quizzes,
           SUM(CASE WHEN q.status = 'published' THEN 1 ELSE 0 END) as published_quizzes,
           COUNT(qa.id) as total_attempts
    FROM users u
    LEFT JOIN quizzes q ON u.id = q.created_by
    LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id
    WHERE u.id = $userId
")->fetch(PDO::FETCH_ASSOC);

$teacherData = json_decode($teacher['teacher_data'] ?? '{}', true);
$recentQuizzes = $db->pdo->query("
    SELECT q.id, q.title, q.status, 
           COUNT(qa.id) as attempt_count,
           AVG(qa.score) as avg_score
    FROM quizzes q
    LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id
    WHERE q.created_by = $userId
    GROUP BY q.id
    ORDER BY q.created_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Profile - Quizify</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .progress-ring__circle {
      transition: stroke-dashoffset 0.5s;
      transform: rotate(-90deg);
      transform-origin: 50% 50%;
    }
  </style>
</head>
<body class="bg-gray-50">
  <!-- Header -->
  <header class="bg-blue-700 text-white shadow-md">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold">Quizify Teacher Profile</h1>
      <nav>
        <a href="dashboard_teacher.php" class="text-white hover:text-blue-200 ml-4">
          <i class="fas fa-chalkboard-teacher mr-1"></i> Dashboard
        </a>
      </nav>
    </div>
  </header>

  <main class="container mx-auto px-4 py-8">
    <!-- Profile Card -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
      <div class="p-6 md:p-8">
        <div class="flex flex-col md:flex-row items-start md:items-center">
          <div class="mr-6 mb-4 md:mb-0">
            <div class="w-24 h-24 rounded-full bg-blue-100 flex items-center justify-center">
              <i class="fas fa-chalkboard-teacher text-blue-600 text-4xl"></i>
            </div>
          </div>
          <div class="flex-1">
            <h2 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($teacher['full_name']) ?></h2>
            <p class="text-gray-600 mb-2">
              <i class="fas fa-envelope mr-2 text-blue-500"></i>
              <?= htmlspecialchars($teacher['email']) ?>
            </p>
            <?php if (!empty($teacherData['subjects'])): ?>
              <p class="text-gray-600">
                <i class="fas fa-book-open mr-2 text-blue-500"></i>
                Subjects: <?= htmlspecialchars(implode(', ', (array)$teacherData['subjects'])) ?>
              </p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-blue-500">
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Quizzes Created</h3>
        <p class="text-3xl font-bold text-blue-600"><?= $teacher['total_quizzes'] ?? 0 ?></p>
        <p class="text-sm text-gray-500 mt-1">
          <?= $teacher['published_quizzes'] ?? 0 ?> published
        </p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-green-500">
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Attempts</h3>
        <p class="text-3xl font-bold text-green-600"><?= $teacher['total_attempts'] ?? 0 ?></p>
        <p class="text-sm text-gray-500 mt-1">by students</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-purple-500">
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Average Rating</h3>
        <div class="flex items-center">
          <div class="relative w-16 h-16 mr-3">
            <svg class="w-full h-full" viewBox="0 0 36 36">
              <path
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
                fill="none"
                stroke="#e6e6e6"
                stroke-width="3"
              />
              <path
                class="progress-ring__circle"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
                fill="none"
                stroke="#8b5cf6"
                stroke-width="3"
                stroke-dasharray="100, 100"
                stroke-dashoffset="<?= 100 - ($teacherData['avg_rating'] ?? 0) * 20 ?>"
              />
            </svg>
            <span class="absolute inset-0 flex items-center justify-center text-sm font-bold text-purple-700">
              <?= number_format($teacherData['avg_rating'] ?? 0, 1) ?>/5
            </span>
          </div>
          <div>
            <div class="flex items-center text-yellow-400">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="fas fa-star <?= $i <= ($teacherData['avg_rating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
              <?php endfor; ?>
            </div>
            <p class="text-sm text-gray-500 mt-1">
              <?= $teacherData['rating_count'] ?? 0 ?> ratings
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Quizzes -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
      <div class="p-6 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
          <i class="fas fa-clipboard-list mr-2 text-blue-600"></i>
          Your Recent Quizzes
        </h2>
        <a href="create_quiz.php" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
          <i class="fas fa-plus mr-1"></i> Create New
        </a>
      </div>
      <div class="divide-y divide-gray-200">
        <?php if (empty($recentQuizzes)): ?>
          <div class="p-6 text-center text-gray-500">
            No quizzes created yet
            <a href="create_quiz.php" class="text-blue-600 hover:underline block mt-2">
              Create your first quiz
            </a>
          </div>
        <?php else: ?>
          <?php foreach ($recentQuizzes as $quiz): ?>
            <div class="p-4 hover:bg-gray-50 transition-colors">
              <div class="flex justify-between items-center">
                <div>
                  <h3 class="font-medium text-gray-800">
                    <a href="edit_quiz.php?id=<?= $quiz['id'] ?>" class="hover:text-blue-600 hover:underline">
                      <?= htmlspecialchars($quiz['title']) ?>
                    </a>
                  </h3>
                  <div class="flex items-center mt-1">
                    <span class="text-xs px-2 py-1 rounded-full 
                      <?= $quiz['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                      <?= ucfirst($quiz['status']) ?>
                    </span>
                    <?php if ($quiz['attempt_count'] > 0): ?>
                      <span class="text-xs text-gray-500 ml-3">
                        <i class="fas fa-users mr-1"></i>
                        <?= $quiz['attempt_count'] ?> attempts
                      </span>
                      <span class="text-xs text-gray-500 ml-3">
                        <i class="fas fa-chart-bar mr-1"></i>
                        Avg: <?= round($quiz['avg_score']) ?>%
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-xl font-semibold text-gray-800 flex items-center">
            <i class="fas fa-bolt mr-2 text-blue-600"></i>
            Quick Actions
          </h2>
        </div>
        <div class="p-6 grid grid-cols-2 gap-4">
          <a href="create_quiz.php" class="p-3 bg-blue-50 rounded-lg text-center hover:bg-blue-100 transition-colors">
            <i class="fas fa-plus-circle text-blue-600 text-2xl mb-2"></i>
            <p class="text-sm font-medium">New Quiz</p>
          </a>
          <a href="quiz_library.php" class="p-3 bg-green-50 rounded-lg text-center hover:bg-green-100 transition-colors">
            <i class="fas fa-book-open text-green-600 text-2xl mb-2"></i>
            <p class="text-sm font-medium">Quiz Library</p>
          </a>
          
          <a href="reports.php" class="p-3 bg-yellow-50 rounded-lg text-center hover:bg-yellow-100 transition-colors">
            <i class="fas fa-chart-pie text-yellow-600 text-2xl mb-2"></i>
            <p class="text-sm font-medium">Reports</p>
          </a>
        </div>
      </div>

      <!-- Recent Feedback -->
      <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-xl font-semibold text-gray-800 flex items-center">
            <i class="fas fa-comment-alt mr-2 text-blue-600"></i>
            Recent Feedback
          </h2>
        </div>
        <div class="p-6">
          <?php
          $feedback = $db->pdo->query("
            SELECT f.rating, f.comment, f.created_at, u.full_name as student_name
            FROM feedback f
            JOIN users u ON f.user_id = u.id
            WHERE f.quiz_id IN (SELECT id FROM quizzes WHERE created_by = $userId)
            ORDER BY f.created_at DESC
            LIMIT 3
          ")->fetchAll(PDO::FETCH_ASSOC);
          ?>
          
          <?php if (empty($feedback)): ?>
            <p class="text-gray-500 text-center py-4">No feedback received yet</p>
          <?php else: ?>
            <div class="space-y-4">
              <?php foreach ($feedback as $item): ?>
                <div class="border-l-4 border-blue-500 pl-4 py-2">
                  <div class="flex justify-between items-start">
                    <div>
                      <p class="font-medium text-gray-800"><?= htmlspecialchars($item['student_name']) ?></p>
                      <div class="flex items-center text-yellow-400 text-sm mt-1">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                          <i class="fas fa-star <?= $i <= $item['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                        <?php endfor; ?>
                      </div>
                    </div>
                    <span class="text-xs text-gray-500">
                      <?= date('M j, Y', strtotime($item['created_at'])) ?>
                    </span>
                  </div>
                  <?php if (!empty($item['comment'])): ?>
                    <p class="text-gray-600 mt-2 text-sm">"<?= htmlspecialchars($item['comment']) ?>"</p>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <script>
    // Simple animation for the progress ring
    document.addEventListener('DOMContentLoaded', function() {
      const circle = document.querySelector('.progress-ring__circle');
      if (circle) {
        // Animation already handled by CSS transition
      }
    });
  </script>
</body>
</html>