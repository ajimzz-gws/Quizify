<?php
require_once 'bootstrap.php';
$auth->requireRole('teacher');

$backUrl = $_GET['from'] ?? 'dashboard_teacher.php';

// Handle quiz deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $quizId = (int)$_GET['id'];
    
    // Verify teacher owns the quiz before deletion
    $stmt = $db->pdo->prepare("SELECT created_by FROM quizzes WHERE id = ?");
    $stmt->execute([$quizId]);
    $creatorId = $stmt->fetchColumn();
    
    if ($creatorId == $_SESSION['user_id']) {
        $db->pdo->prepare("DELETE FROM quizzes WHERE id = ?")->execute([$quizId]);
        $_SESSION['message'] = "Quiz deleted successfully";
    } else {
        $_SESSION['error'] = "You can only delete your own quizzes";
    }
    
    header("Location: quizzes.php");
    exit;
}

// Fetch quizzes for the current teacher with stats
$userId = $_SESSION['user_id'];
$quizzes = $db->pdo->query("
    SELECT q.*, 
           (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.id) as attempt_count,
           (SELECT AVG(score) FROM quiz_attempts WHERE quiz_id = q.id) as avg_score
    FROM quizzes q
    WHERE q.created_by = $userId
    ORDER BY q.status DESC, q.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Library - Teacher Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .quiz-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .status-badge {
            top: -10px;
            right: -10px;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex flex-col min-h-screen">
        <header class="sticky top-0 z-10 bg-white shadow px-6 py-4 flex items-center">
        <a href="<?= $backUrl ?>" class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-arrow-left"></i> Back 
        </a>
        </header>
        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Page Header -->
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Quiz Library</h1>
                        <p class="text-gray-600">Manage and organize your quizzes</p>
                    </div>
                    
                    <a href="create_quiz.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-2"></i> Create New Quiz
                    </a>
                </div>

                <!-- Messages -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg">
                        <?= $_SESSION['message'] ?>
                        <?php unset($_SESSION['message']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg">
                        <?= $_SESSION['error'] ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <!-- Quiz Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php if (count($quizzes) > 0): ?>
                        <?php foreach ($quizzes as $quiz): ?>
                            <?php
                            $questions = json_decode($quiz['questions_json'], true);
                            $questionCount = is_array($questions) ? count($questions) : 0;
                            ?>
                            <div class="quiz-card relative bg-white rounded-xl shadow-md overflow-hidden transition duration-300">
                                <!-- Status Badge -->
                                <span class="status-badge absolute px-3 py-1 text-xs font-semibold rounded-full 
                                    <?= $quiz['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                    <?= ucfirst($quiz['status']) ?>
                                </span>
                                
                                <!-- Quiz Content -->
                                <div class="p-6">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-2"><?= htmlspecialchars($quiz['title']) ?></h3>
                                    <p class="text-gray-600 mb-4"><?= htmlspecialchars($quiz['description']) ?></p>
                                    
                                    <div class="flex justify-between text-sm text-gray-500 mb-4">
                                        <span><i class="fas fa-question-circle mr-1"></i> <?= $questionCount ?> questions</span>
                                        <span><i class="fas fa-users mr-1"></i> <?= $quiz['attempt_count'] ?> attempts</span>
                                        <span><i class="fas fa-chart-line mr-1"></i> <?= $quiz['avg_score'] ? round($quiz['avg_score'], 1) : 'N/A' ?>% avg</span>
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        <a href="view_attempts.php?id=<?= $quiz['id'] ?>" 
                                           class="flex-1 text-center py-2 px-3 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>
                                        <a href="edit_quiz.php?id=<?= $quiz['id'] ?>&from=quiz_library_teacher.php" 
                                           class="flex-1 text-center py-2 px-3 bg-gray-50 text-gray-600 rounded hover:bg-gray-100 transition">
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </a>
                                        <a href="quizzes.php?action=delete&id=<?= $quiz['id'] ?>"
                                           class="flex-1 text-center py-2 px-3 bg-red-50 text-red-600 rounded hover:bg-red-100 transition">
                                            <i class="fas fa-trash mr-1"></i> Delete
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Footer -->
                                <div class="px-6 py-3 bg-gray-50 text-xs text-gray-500">
                                    Created <?= date('M d, Y', strtotime($quiz['created_at'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center py-12">
                            <i class="fas fa-clipboard-list text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900">No quizzes yet</h3>
                            <p class="mt-2 text-gray-500">Get started by creating your first quiz</p>
                            <div class="mt-6">
                                <a href="create_quiz.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    <i class="fas fa-plus mr-2"></i> Create Quiz
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Confirm before deleting
        document.querySelectorAll('a[href*="action=delete"]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this quiz?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>