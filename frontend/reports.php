<?php
require_once 'bootstrap.php';
$auth->requireLogin();

// Only teachers can access reports
if ($_SESSION['user_role'] !== 'teacher') {
    header("Location: dashboard.php");
    exit();
}

// Get quiz reports with attempt details
$reports = $db->pdo->query("
    SELECT 
        q.id AS quiz_id,
        q.title AS quiz_title,
        COUNT(a.id) AS attempt_count,
        AVG(a.score) AS average_score,
        MAX(a.score) AS highest_score,
        MIN(a.score) AS lowest_score
    FROM quizzes q
    LEFT JOIN attempts a ON q.id = a.quiz_id
    WHERE q.created_by = {$_SESSION['user_id']}
    GROUP BY q.id
    ORDER BY q.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Get recent attempts for each quiz
foreach ($reports as &$report) {
    $stmt = $db->pdo->prepare("
        SELECT u.username, a.score, a.completed_at 
        FROM attempts a
        JOIN users u ON a.user_id = u.id
        WHERE a.quiz_id = ?
        ORDER BY a.completed_at DESC
        LIMIT 3
    ");
    $stmt->execute([$report['quiz_id']]);
    $report['recent_attempts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
unset($report); // Break reference
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quiz Reports</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50">
  <header class="bg-blue-700 text-white py-5 shadow-md">
    <div class="container mx-auto px-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold">Quiz Reports</h1>
      <a href="dashboard_teacher.php" class="flex items-center text-white hover:text-blue-200 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Back to Dashboard
      </a>
    </div>
  </header>

  <main class="container mx-auto px-4 py-8">
    <?php if (empty($reports)): ?>
      <div class="text-center py-12 text-gray-600">
        <p class="text-lg">No quiz reports available yet.</p>
        <a href="dashboard_teacher.php" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
          Return to Dashboard
        </a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 gap-6">
        <?php foreach ($reports as $report): ?>
          <div class="bg-white rounded-lg shadow-md overflow-hidden border-l-4 border-blue-600">
            <div class="p-6">
              <h3 class="text-xl font-semibold text-blue-800 mb-3"><?= htmlspecialchars($report['quiz_title']) ?></h3>
              
              <!-- Summary Stats -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                <div class="bg-blue-50 p-3 rounded-lg">
                  <p class="text-sm text-gray-500">Attempts</p>
                  <p class="text-xl font-bold text-blue-700"><?= $report['attempt_count'] ?></p>
                </div>
                
                <div class="bg-green-50 p-3 rounded-lg">
                  <p class="text-sm text-gray-500">Avg. Score</p>
                  <p class="text-xl font-bold text-green-700">
                    <?= $report['attempt_count'] > 0 ? round($report['average_score'], 1) . '%' : 'N/A' ?>
                  </p>
                </div>
                
                <div class="bg-yellow-50 p-3 rounded-lg">
                  <p class="text-sm text-gray-500">Highest</p>
                  <p class="text-xl font-bold text-yellow-700">
                    <?= $report['attempt_count'] > 0 ? round($report['highest_score'], 1) . '%' : 'N/A' ?>
                  </p>
                </div>
                
                <div class="bg-red-50 p-3 rounded-lg">
                  <p class="text-sm text-gray-500">Lowest</p>
                  <p class="text-xl font-bold text-red-700">
                    <?= $report['attempt_count'] > 0 ? round($report['lowest_score'], 1) . '%' : 'N/A' ?>
                  </p>
                </div>
              </div>
              
              <!-- Recent Attempts -->
              <?php if ($report['attempt_count'] > 0): ?>
                <div class="mb-4">
                  <h4 class="font-medium text-gray-700 mb-2">Recent Attempts:</h4>
                  <div class="space-y-2">
                    <?php foreach ($report['recent_attempts'] as $attempt): ?>
                      <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded">
                        <span class="font-medium"><?= htmlspecialchars($attempt['username']) ?></span>
                        <span class="text-blue-600 font-bold"><?= round($attempt['score'], 1) ?>%</span>
                        <span class="text-sm text-gray-500">
                          <?= date('M j, g:i a', strtotime($attempt['completed_at'])) ?>
                        </span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>
              
              <!-- Quick Actions -->
              <div class="flex flex-wrap gap-2">
                <a href="quizzes.php" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors text-sm font-medium">
                  View Quiz
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>