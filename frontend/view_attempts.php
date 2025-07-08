<?php
require_once 'bootstrap.php';
// Make sure this is at the very top of bootstrap.php
session_start();

// Then check if session is active
if (session_status() !== PHP_SESSION_ACTIVE) {
    die('Session initialization failed');
}
$auth->requireRole('teacher');

// Get quiz ID from URL
$quizId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch quiz details
$quiz = $db->pdo->query("
    SELECT q.*, u.full_name as teacher_name 
    FROM quizzes q
    JOIN users u ON q.created_by = u.id
    WHERE q.id = $quizId
")->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    header("Location: quizzes.php?error=quiz_not_found");
    exit;
}

// Check if current teacher owns this quiz
if ($quiz['created_by'] != $_SESSION['user_id']) {
    header("Location: quizzes.php?error=unauthorized");
    exit;
}

// Fetch quiz attempts
$attempts = $db->pdo->query("
    SELECT a.*, u.full_name as student_name, u.email as student_email
    FROM quiz_attempts a
    JOIN users u ON a.student_id = u.id
    WHERE a.quiz_id = $quizId
    ORDER BY a.completed_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Decode questions
$questions = json_decode($quiz['questions_json'], true);

// Calculate statistics
$totalAttempts = count($attempts);
$avgScore = $totalAttempts > 0 ? 
    round(array_sum(array_column($attempts, 'score')) / $totalAttempts, 1) : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Quiz - <?= htmlspecialchars($quiz['title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-blue-600">Quiz Details</h1>
            <div class="relative">
                <button id="profileDropdownBtn" class="flex items-center space-x-2 focus:outline-none">
                    <img src="<?= htmlspecialchars($_SESSION['user']['profile_image'] ?? 'https://placehold.co/128x128/cccccc/333333?text=Teacher') ?>" 
                         alt="Profile" class="w-10 h-10 rounded-full object-cover">
                    <i class="fas fa-caret-down text-gray-600"></i>
                </button>
                <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                    <a href="profile.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Profile</a>
                    <a href="logout.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Logout</a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="max-w-6xl mx-auto">
                <!-- Quiz Info Card -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($quiz['title']) ?></h2>
                            <p class="text-gray-600 mt-2"><?= htmlspecialchars($quiz['description']) ?></p>
                            <p class="text-sm text-gray-500 mt-2">Created by: <?= htmlspecialchars($quiz['teacher_name']) ?></p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                                <?= $quiz['status'] === 'published' ? 'Published' : 'Draft' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm font-medium text-gray-500">Total Questions</p>
                            <p class="text-2xl font-bold text-gray-800"><?= count($questions) ?></p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm font-medium text-gray-500">Total Attempts</p>
                            <p class="text-2xl font-bold text-gray-800"><?= $totalAttempts ?></p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm font-medium text-gray-500">Average Score</p>
                            <p class="text-2xl font-bold text-gray-800"><?= $avgScore ?>%</p>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="flex space-x-8">
                        <button id="questionsTab" class="py-4 px-1 border-b-2 font-medium text-sm border-blue-500 text-blue-600">
                            Questions
                        </button>
                        <button id="attemptsTab" class="py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            Attempts
                        </button>
                    </nav>
                </div>

                <!-- Questions Content -->
                <div id="questionsContent" class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quiz Questions</h3>
                    <div class="space-y-6">
                        <?php foreach ($questions as $index => $question): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <h4 class="font-medium text-gray-800">Question <?= $index + 1 ?></h4>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= count($question['choices']) ?> options
                                </span>
                            </div>
                            <p class="mt-2 text-gray-700"><?= htmlspecialchars($question['question']) ?></p>
                            
                            <div class="mt-4 space-y-2">
                                <?php foreach ($question['choices'] as $i => $choice): ?>
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="radio" 
                                               disabled
                                               <?= $i == $question['correct_answer'] ? 'checked' : '' ?>
                                               class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label class="font-medium text-gray-700 <?= $i == $question['correct_answer'] ? 'text-green-600' : '' ?>">
                                            <?= htmlspecialchars($choice) ?>
                                            <?php if ($i == $question['correct_answer']): ?>
                                            <span class="ml-2 text-green-500">(Correct Answer)</span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Attempts Content -->
                <div id="attemptsContent" class="bg-white rounded-lg shadow-md p-6 hidden">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Student Attempts</h3>
                        <div class="relative">
                            <select id="attemptFilter" class="block appearance-none bg-white border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                <option value="all">All Attempts</option>
                                <option value="passed">Passed Only</option>
                                <option value="failed">Failed Only</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <?php if ($totalAttempts > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($attempts as $attempt): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" src="https://placehold.co/128x128/cccccc/333333?text=<?= substr($attempt['student_name'], 0, 1) ?>" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($attempt['student_name']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($attempt['student_email']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= $attempt['score'] ?>%</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $attempt['score'] >= 70 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= $attempt['score'] >= 70 ? 'Passed' : 'Failed' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('M d, Y h:i A', strtotime($attempt['completed_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="view_attempt.php?id=<?= $attempt['id'] ?>" class="text-blue-600 hover:text-blue-900">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-12">
                        <i class="fas fa-clipboard-list text-4xl text-gray-400 mb-4"></i>
                        <h4 class="text-lg font-medium text-gray-900">No attempts yet</h4>
                        <p class="mt-1 text-sm text-gray-500">This quiz hasn't been attempted by any students.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab switching
        document.getElementById('questionsTab').addEventListener('click', function() {
            document.getElementById('questionsContent').classList.remove('hidden');
            document.getElementById('attemptsContent').classList.add('hidden');
            this.classList.add('border-blue-500', 'text-blue-600');
            this.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            document.getElementById('attemptsTab').classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            document.getElementById('attemptsTab').classList.remove('border-blue-500', 'text-blue-600');
        });

        document.getElementById('attemptsTab').addEventListener('click', function() {
            document.getElementById('attemptsContent').classList.remove('hidden');
            document.getElementById('questionsContent').classList.add('hidden');
            this.classList.add('border-blue-500', 'text-blue-600');
            this.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            document.getElementById('questionsTab').classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            document.getElementById('questionsTab').classList.remove('border-blue-500', 'text-blue-600');
        });

        // Filter attempts
        document.getElementById('attemptFilter').addEventListener('change', function() {
            const filter = this.value;
            const rows = document.querySelectorAll('#attemptsContent tbody tr');
            
            rows.forEach(row => {
                const status = row.querySelector('td:nth-child(4) span').textContent.toLowerCase();
                
                if (filter === 'all' || 
                    (filter === 'passed' && status === 'passed') ||
                    (filter === 'failed' && status === 'failed')) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        });

        // Profile dropdown
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