<?php
require_once 'bootstrap.php';

$auth->preventBackButton();
$auth->requireRole('teacher');

$userId = $_SESSION['user_id'];

$quizzes = $db->pdo->query("
    SELECT q.*, 
           (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.id) as attempt_count,
           (SELECT AVG(score) FROM quiz_attempts WHERE quiz_id = q.id) as avg_score
    FROM quizzes q
    WHERE q.created_by = $userId
    ORDER BY q.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$teacher = $db->pdo->query("SELECT * FROM users WHERE id = $userId")->fetch(PDO::FETCH_ASSOC);

// Decode teacher_data JSON
$teacherData = isset($teacher['teacher_data']) ? json_decode($teacher['teacher_data'], true) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Teacher Dashboard - Quizify</title>

  <!-- Tailwind CSS & Fonts -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f9fafb;
    }
  </style>

  <script>
    window.addEventListener("pageshow", function(event) {
      if (event.persisted) {
        window.location.reload();
      }
    });
  </script>
</head>

<body>
  <div class="flex flex-col min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-blue-600">Quizify Teacher Dashboard</h1>
      <div class="relative">
        <button id="profileDropdownBtn" class="flex items-center space-x-2 focus:outline-none">
          <img src="<?= htmlspecialchars($teacher['profile_image'] ?? 'https://placehold.co/128x128/cccccc/333333?text=Teacher') ?>" 
               alt="Profile" class="w-10 h-10 rounded-full object-cover">
          <i class="fas fa-caret-down text-gray-600"></i>
        </button>
        <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
          <a href="profile.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Profile</a>
          <a href="logout.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Logout</a>
        </div>
      </div>
    </header>

    <!-- Layout -->
    <div class="flex flex-1">
      <!-- Sidebar -->
      <aside class="w-64 bg-blue-700 text-white p-6">
        <div class="flex items-center mb-8 bg-blue-800 p-3 rounded-md">
          <img src="<?= htmlspecialchars($teacher['profile_image'] ?? 'https://placehold.co/128x128/cccccc/333333?text=Teacher') ?>" 
               alt="Profile" class="w-12 h-12 rounded-full mr-3 object-cover">
          <div>
            <p class="font-semibold"><?= htmlspecialchars($teacherData['name'] ?? 'No Name') ?></p>
            <p class="text-sm opacity-90"><?= htmlspecialchars($teacherData['email'] ?? 'No Email') ?></p>
          </div>
        </div>
        
        <nav>
          <ul class="space-y-3">
            <li>
              <a href="quiz_library_teacher.php" class="flex items-center space-x-2 py-2 px-3 hover:bg-blue-600 rounded-md">
                <i class="fas fa-book"></i>
                <span>Quiz Library</span>
              </a>
            </li>
            <li>
              <a href="create_quiz.php" class="flex items-center space-x-2 py-2 px-3 hover:bg-blue-600 rounded-md">
                <i class="fas fa-plus-circle"></i>
                <span>Create Quiz</span>
              </a>
            </li>
            <li>
              <a href="reports.php" class="flex items-center space-x-2 py-2 px-3 hover:bg-blue-600 rounded-md">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
              </a>
            </li>
          </ul>
        </nav>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 p-8">
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-3xl font-bold text-blue-600"><?= count($quizzes) ?></h3>
            <p class="text-gray-600">Quizzes Created</p>
          </div>
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-3xl font-bold text-blue-600"><?= array_sum(array_column($quizzes, 'attempt_count')) ?></h3>
            <p class="text-gray-600">Total Attempts</p>
          </div>
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-3xl font-bold text-blue-600">
              <?= count($quizzes) > 0 ? round(array_sum(array_column($quizzes, 'avg_score')) / count($quizzes), 1) : '0' ?>%
            </h3>
            <p class="text-gray-600">Average Score</p>
          </div>
        </div>

        <!-- Quizzes Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Your Quizzes</h2>
            <a href="quizzes.php" 
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
              <i class="fas fa-list mr-2"></i> All Quizzes
            </a>
            <a href="create_quiz.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
              <i class="fas fa-plus mr-2"></i>Create New Quiz
            </a>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Questions</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attempts</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Score</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($quizzes as $quiz): ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-medium text-gray-900"><?= htmlspecialchars($quiz['title']) ?></div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <?= count(json_decode($quiz['questions_json'])) ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <?= $quiz['attempt_count'] ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <?php 
                    if ($quiz['avg_score'] !== null) {
                        echo round($quiz['avg_score'], 1) . '%';
                    } else {
                        echo '0%'; // or 'N/A' if you prefer
                    }
                    ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap space-x-2">
                    <a href="view_attempts.php?id=<?= $quiz['id'] ?>" 
                       class="text-blue-600 hover:text-blue-800">
                      <i class="fas fa-eye mr-1"></i>View
                    </a>
                    <a href="edit_quiz.php?id=<?= $quiz['id'] ?>&from=dashboard_teacher.php" 
                       class="text-green-600 hover:text-green-800">
                      <i class="fas fa-edit mr-1"></i>Edit
                    </a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
    document.getElementById('profileDropdownBtn').addEventListener('click', function() {
      document.getElementById('profileDropdown').classList.toggle('hidden');
    });

    document.addEventListener('click', function(event) {
      const dropdown = document.getElementById('profileDropdown');
      const button = document.getElementById('profileDropdownBtn');

      if (!button.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.add('hidden');
      }
    });
  </script>
</body>
</html>
