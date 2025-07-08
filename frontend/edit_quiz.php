<?php
require_once 'bootstrap.php';
$auth->requireRole('teacher');

// Get quiz ID from URL
$quizId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch quiz data
$quiz = $db->pdo->query("
    SELECT * FROM quizzes 
    WHERE id = $quizId AND created_by = {$_SESSION['user_id']}
")->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    $_SESSION['error'] = "Quiz not found or you don't have permission to edit it";
    header("Location: quizzez.php");
    exit;
}

// Decode questions
$questions = json_decode($quiz['questions_json'], true);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $updatedQuestions = [];
        
        foreach ($_POST['questions'] as $index => $questionData) {
            // Validate data
            if (empty($questionData['question'])) {
                throw new Exception("Question text cannot be empty for question " . ($index + 1));
            }
            
            $choices = array_values(array_filter($questionData['choices']));
            if (count($choices) < 2) {
                throw new Exception("Question " . ($index + 1) . " needs at least 2 options");
            }
            
            if (!isset($questionData['correct_answer'])) {
                throw new Exception("Please select correct answer for question " . ($index + 1));
            }
            
            $updatedQuestions[] = [
                'question' => trim(htmlspecialchars($questionData['question'])),
                'choices' => array_map('trim', $choices),
                'correct_answer' => (int)$questionData['correct_answer']
            ];
        }
        
        // Update database
        $stmt = $db->pdo->prepare("UPDATE quizzes SET 
            title = :title,
            description = :description,
            questions_json = :questions_json,
            status = :status
            WHERE id = :quiz_id");
        
        $stmt->execute([
            ':title' => trim(htmlspecialchars($_POST['title'])),
            ':description' => trim(htmlspecialchars($_POST['description'])),
            ':questions_json' => json_encode($updatedQuestions),
            ':status' => $_POST['status'],
            ':quiz_id' => $quizId
        ]);
        
        $_SESSION['message'] = "Quiz updated successfully!";
        header("Location: quizzes.php");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: edit_quiz.php?id=$quizId");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz - <?= htmlspecialchars($quiz['title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .question-card {
            transition: all 0.2s ease;
        }
        .question-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .border-red-500 {
            border-color: #ef4444;
            animation: pulse 0.5s ease-in-out;
        }
        @keyframes pulse {
            0% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            100% { transform: translateX(0); }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex flex-col min-h-screen">
        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md overflow-hidden">
                <!-- Form Header -->
                <div class="bg-blue-600 px-6 py-4 text-white">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-bold">Edit Quiz</h1>
                        <span class="px-3 py-1 bg-blue-700 rounded-full text-sm font-semibold">
                            <?= ucfirst($quiz['status']) ?>
                        </span>
                    </div>
                    <p class="text-blue-100">Make changes to your quiz questions and settings</p>
                </div>

                <!-- Form -->
                <form method="post" class="p-6">
                    <?php if (isset($_SESSION['error'])): ?>
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                        <?= $_SESSION['error'] ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Quiz Info -->
                    <div class="space-y-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Quiz Title</label>
                            <input type="text" id="title" name="title" required
                                   value="<?= htmlspecialchars($quiz['title']) ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea id="description" name="description" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($quiz['description']) ?></textarea>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="status" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="draft" <?= $quiz['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="published" <?= $quiz['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                            </select>
                        </div>
                    </div>

                    <!-- Questions Section -->
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Questions</h2>
                        <div id="questions-container" class="space-y-6">
                            <?php foreach ($questions as $index => $question): ?>
                            <div class="question-card border border-gray-200 rounded-lg p-6" data-question-index="<?= $index ?>">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="font-medium text-gray-800">Question <?= $index + 1 ?></h3>
                                    <button type="button" onclick="removeQuestion(this)" 
                                            class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Question Text</label>
                                    <input type="text" name="questions[<?= $index ?>][question]" required
                                           value="<?= htmlspecialchars($question['question']) ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Answer Choices</label>
                                    <div class="space-y-3">
                                        <?php foreach ($question['choices'] as $i => $choice): ?>
                                        <div class="flex items-center">
                                            <input type="radio" name="questions[<?= $index ?>][correct_answer]" 
                                                   value="<?= $i ?>" 
                                                   <?= $i == $question['correct_answer'] ? 'checked' : '' ?>
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                            <input type="text" name="questions[<?= $index ?>][choices][<?= $i ?>]" required
                                                   value="<?= htmlspecialchars($choice) ?>"
                                                   class="ml-3 flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="mt-4 flex justify-end">
                                    <button type="button" onclick="updateQuestion(this)" 
                                            class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition">
                                        <i class="fas fa-save mr-2"></i> Update Options
                                    </button>
                                    <div id="question-<?= $index ?>-status" class="text-xs text-green-600 mt-2 hidden ml-3 self-center">
                                        <i class="fas fa-check-circle"></i> Options saved
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mt-6 flex justify-between">
                            <button type="button" onclick="addQuestion()"
                                    class="px-6 py-2 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 transition">
                                <i class="fas fa-plus mr-2"></i> Add Question
                            </button>
                            <button type="submit"
                                    class="px-6 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                                <i class="fas fa-save mr-2"></i> Save Quiz
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Add new question
        function addQuestion() {
            const container = document.getElementById('questions-container');
            const newIndex = container.children.length;
            
            const questionHTML = `
                <div class="question-card border border-gray-200 rounded-lg p-6" data-question-index="${newIndex}">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-medium text-gray-800">Question ${newIndex + 1}</h3>
                        <button type="button" onclick="removeQuestion(this)" 
                                class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Question Text</label>
                        <input type="text" name="questions[${newIndex}][question]" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Answer Choices</label>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="radio" name="questions[${newIndex}][correct_answer]" 
                                       value="0" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <input type="text" name="questions[${newIndex}][choices][0]" required
                                       class="ml-3 flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="questions[${newIndex}][correct_answer]" 
                                       value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <input type="text" name="questions[${newIndex}][choices][1]" required
                                       class="ml-3 flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="questions[${newIndex}][correct_answer]" 
                                       value="2" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <input type="text" name="questions[${newIndex}][choices][2]" required
                                       class="ml-3 flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="questions[${newIndex}][correct_answer]" 
                                       value="3" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <input type="text" name="questions[${newIndex}][choices][3]" required
                                       class="ml-3 flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="button" onclick="updateQuestion(this)" 
                                class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition">
                            <i class="fas fa-save mr-2"></i> Update Options
                        </button>
                        <div id="question-${newIndex}-status" class="text-xs text-green-600 mt-2 hidden ml-3 self-center">
                            <i class="fas fa-check-circle"></i> Options saved
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', questionHTML);
        }

        // Remove question
        function removeQuestion(button) {
            if (confirm('Are you sure you want to remove this question?')) {
                const questionCard = button.closest('.question-card');
                questionCard.remove();
                
                // Reindex remaining questions
                const container = document.getElementById('questions-container');
                Array.from(container.children).forEach((card, index) => {
                    card.setAttribute('data-question-index', index);
                    card.querySelector('h3').textContent = `Question ${index + 1}`;
                    
                    // Update input names
                    const inputs = card.querySelectorAll('input');
                    inputs.forEach(input => {
                        if (input.name) {
                            input.name = input.name.replace(/questions\[\d+\]/, `questions[${index}]`);
                        }
                    });
                });
            }
        }

        // Update question
        function updateQuestion(button) {
            const questionCard = button.closest('.question-card');
            const inputs = questionCard.querySelectorAll('input[type="text"]');
            let allFilled = true;
            
            // Validate all options are filled
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    allFilled = false;
                    input.classList.add('border-red-500');
                } else {
                    input.classList.remove('border-red-500');
                }
            });
            
            if (!allFilled) {
                alert('Please fill in all answer options before updating');
                return;
            }
            
            // Visual feedback
            const statusElement = questionCard.querySelector('[id$="-status"]');
            statusElement.classList.remove('hidden');
            
            button.innerHTML = '<i class="fas fa-check mr-2"></i> Updated';
            button.classList.remove('bg-blue-100', 'text-blue-700');
            button.classList.add('bg-green-100', 'text-green-700');
            button.disabled = true;
            
            setTimeout(() => {
                statusElement.classList.add('hidden');
                button.innerHTML = '<i class="fas fa-save mr-2"></i> Update Options';
                button.classList.remove('bg-green-100', 'text-green-700');
                button.classList.add('bg-blue-100', 'text-blue-700');
                button.disabled = false;
            }, 2000);
        }
    </script>
</body>
</html>