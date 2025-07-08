<?php
require_once 'bootstrap.php';

$auth->preventBackButton();
$auth->requireRole('student');

$userId = $_SESSION['user_id'];

// Fetch student data
$student = $db->pdo->query("
    SELECT *
    FROM users
    WHERE id = $userId
")->fetch(PDO::FETCH_ASSOC);

$studentData = isset($student['student_data']) ? json_decode($student['student_data'], true) : [
    'class' => 'Not assigned',
    'parent_contact' => '',
    'emergency_contact' => ''
];

// Fetch quiz attempts
$attempts = $db->pdo->query("
    SELECT q.title, q.category, qa.score, qa.submitted_at
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    WHERE qa.user_id = $userId
    ORDER BY qa.submitted_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$totalQuizzes = count($attempts);
$averageScore = $totalQuizzes > 0 ? round(array_sum(array_column($attempts, 'score')) / $totalQuizzes, 1) : 0;
$bestScore = $totalQuizzes > 0 ? max(array_column($attempts, 'score')) : 0;

function calculateGrade($score) {
    if ($score >= 90) return 'A+';
    if ($score >= 80) return 'A';
    if ($score >= 75) return 'B+';
    if ($score >= 70) return 'B';
    if ($score >= 65) return 'C+';
    if ($score >= 60) return 'C';
    if ($score >= 50) return 'D';
    return 'E';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard - Quizify</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <script>
    window.addEventListener("pageshow", function(event) {
        if (event.persisted) window.location.reload();
    });
  </script>
</head>
<body class="relative font-sans antialiased bg-cover bg-center min-h-screen"
      style="background-image: url('https://img.freepik.com/free-vector/blue-curve-background_53876-113112.jpg');">

  <!-- Overlay -->
  <div class="absolute inset-0 bg-blue-900 opacity-60 z-0"></div>

  <!-- Page Content -->
  <div class="relative z-10 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white bg-opacity-95 w-full max-w-6xl shadow-xl rounded-xl overflow-hidden flex flex-col">

      <!-- Header -->
      <header class="p-4 bg-white flex justify-between items-center sticky top-0 z-50 border-b border-gray-200">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">STUDENT DASHBOARD</h1>
        <div class="relative">
          <button onclick="toggleDropdown()" class="flex items-center gap-2 p-2 rounded-md hover:bg-gray-100 cursor-pointer">
            <img src="images/quzify4.png" alt="Quizify Logo" class="h-10">
            <i class="fas fa-caret-down text-gray-600"></i>
          </button>
          <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-40 bg-white rounded-md shadow-md border border-gray-200">
            <a href="profile_student.php" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
            <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100">Log out</a>
          </div>
        </div>
      </header>

      <script>
        function toggleDropdown() {
          const menu = document.getElementById('dropdownMenu');
          menu.classList.toggle('hidden');
          document.addEventListener('click', function closeMenu(e) {
            if (!e.target.closest('.relative')) {
              menu.classList.add('hidden');
              document.removeEventListener('click', closeMenu);
            }
          });
        }
      </script>

      <!-- Main Layout -->
      <div class="flex flex-col lg:flex-row flex-grow">
        <!-- Sidebar -->
        <aside class="w-full lg:w-64 bg-blue-700 p-6 text-white">
          <div class="flex items-center mb-8 bg-blue-800 p-3 rounded-md">
            <i class="fas fa-user-circle text-4xl mr-3"></i>
            <div>
              <p class="font-semibold text-lg">Welcome, <?= htmlspecialchars($student['full_name']) ?>!</p>
              <p class="text-sm opacity-90">Class: <?= htmlspecialchars($studentData['class']) ?></p>
            </div>
          </div>

          <h3 class="text-lg font-semibold mb-4">Class: <?= htmlspecialchars($studentData['class']) ?></h3>

          <nav>
            <ul>
              <li class="mb-4">
                <a href="#" class="text-white hover:text-blue-200 block py-2 text-lg font-medium">Past Quiz</a>
              </li>
              <li class="mb-4">
                <select class="block w-full bg-blue-600 border border-blue-500 text-white py-2 px-4 pr-8 rounded-md leading-tight focus:outline-none">
                  <option>Select Subject</option>
                  <?php
                  $subjects = $db->pdo->query("SELECT DISTINCT category FROM quizzes")->fetchAll(PDO::FETCH_COLUMN);
                  foreach ($subjects as $subject): ?>
                    <option><?= htmlspecialchars($subject) ?></option>
                  <?php endforeach; ?>
                </select>
              </li>
            </ul>
          </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-grow p-6 bg-white bg-opacity-90">
          <!-- Welcome Card -->
          <div class="bg-blue-100 p-4 rounded-lg mb-8 flex flex-col sm:flex-row items-start sm:items-center">
            <i class="fas fa-user-circle text-blue-700 text-3xl mr-4 mb-2 sm:mb-0"></i>
            <div>
              <p class="font-semibold text-blue-800 text-lg">Welcome, <?= htmlspecialchars($student['full_name']) ?>!</p>
              <p class="text-blue-600">Class: <?= htmlspecialchars($studentData['class']) ?></p>
            </div>
          </div>

          <!-- Stats -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Quizzes Taken</h3>
              <p class="text-3xl font-bold text-blue-600"><?= $totalQuizzes ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Average Score</h3>
              <p class="text-3xl font-bold text-blue-600"><?= $averageScore ?>%</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Best Score</h3>
              <p class="text-3xl font-bold text-blue-600"><?= $bestScore ?>%</p>
            </div>
          </div>

          <!-- Quiz Results Table -->
          <h2 class="text-xl font-semibold text-gray-800 mb-4">Your Quiz Results</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
              <thead>
                <tr class="bg-gray-100 border-b border-gray-200">
                  <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Subject</th>
                  <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Quiz Name</th>
                  <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Score</th>
                  <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Grade</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($attempts as $attempt): ?>
                  <tr class="border-b border-gray-200">
                    <td class="py-3 px-4 text-sm text-gray-700"><?= htmlspecialchars($attempt['category']) ?></td>
                    <td class="py-3 px-4 text-sm text-gray-700"><?= htmlspecialchars($attempt['title']) ?></td>
                    <td class="py-3 px-4 text-sm text-gray-700"><?= $attempt['score'] ?? 'N/A' ?></td>
                    <td class="py-3 px-4 text-sm text-gray-700"><?= isset($attempt['score']) ? calculateGrade($attempt['score']) : 'N/A' ?></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($attempts)): ?>
                  <tr>
                    <td colspan="4" class="py-4 text-center text-gray-500">No quiz attempts yet</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </main>
      </div>

    </div>
  </div>
</body>
</html>
