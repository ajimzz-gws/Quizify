<?php
require_once 'bootstrap.php';
$auth->requireRole('teacher');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questions = [];
    foreach ($_POST['questions'] as $q) {
        $questions[] = [
            'question' => $q['question'],
            'choices' => $q['choices'],
            'correct_answer' => $q['correct_answer'] ?? 0 // Default to first choice if not specified
        ];
    }

    $data = [
        'title' => $_POST['title'],
        'description' => $_POST['description'] ?? null,
        'category' => $_POST['category'],
        'created_by' => $_SESSION['user_id'],
        'questions_json' => json_encode($questions),
        'status' => ($_POST['action'] === 'publish') ? 'published' : 'draft',
        'created_at' => date('Y-m-d H:i:s')
    ];

    $quizId = $db->insert('quizzes', $data);
    
    if ($_POST['action'] === 'publish') {
        header("Location: quiz_preview.php?id=$quizId&published=1");
    } else {
        header("Location: quiz_preview.php?id=$quizId");
    }
    exit;
}

// Get teacher info for header
$teacher = $db->pdo->query("SELECT * FROM users WHERE id = {$_SESSION['user_id']}")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz - Quizify</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .step {
            position: relative;
            padding-bottom: 10px;
        }
        .step.active {
            font-weight: 600;
            color: #0288d1;
        }
        .step.active:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #0288d1;
            border-radius: 3px;
        }
        .question-card {
            background: #f8fafc;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
            border: 1px solid #e2e8f0;
        }
        .choices li {
            padding: 8px;
            margin: 4px 0;
            background: white;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-blue-600">Create New Quiz</h1>
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

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md overflow-hidden">
                <!-- Progress Steps -->
                <div class="steps flex justify-around p-6 border-b">
                    <span class="step active">1. Quiz Info</span>
                    <span class="step">2. Add Questions</span>
                    <span class="step">3. Review & Publish</span>
                </div>

                <!-- Quiz Form -->
                <form method="post" id="quiz-form" class="p-6">
                    <!-- Step 1: Quiz Info -->
                    <div id="step1" class="space-y-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Quiz Title</label>
                            <input type="text" id="title" name="title" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter quiz title">
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select id="category" name="category" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select a category</option>
                                <option value="Math">Math</option>
                                <option value="Science">Science</option>
                                <option value="History">History</option>
                                <option value="Language">Language</option>
                                <option value="General">General</option>
                            </select>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea id="description" name="description" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Brief description of the quiz"></textarea>
                        </div>

                        <div>
                            <label for="totalQuestions" class="block text-sm font-medium text-gray-700 mb-1">Total Questions</label>
                            <input type="number" id="totalQuestions" name="totalQuestions" min="1" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Number of questions">
                        </div>

                        <div class="flex justify-end">
                            <button type="button" onclick="nextStep()"
                                    class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                                Next
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Add Questions -->
                    <div id="step2" class="hidden space-y-6">
                        <p id="questionTracker" class="text-center text-gray-600">0 / ? questions added</p>
                        
                        <div>
                            <label for="question" class="block text-sm font-medium text-gray-700 mb-1">Question</label>
                            <input type="text" id="question" name="question"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter your question">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Answer Choices</label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input type="radio" name="correct_answer" value="0" class="mr-2">
                                    <input type="text" id="answer1" name="choices[]"
                                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Choice 1">
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="correct_answer" value="1" class="mr-2">
                                    <input type="text" id="answer2" name="choices[]"
                                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Choice 2">
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="correct_answer" value="2" class="mr-2">
                                    <input type="text" id="answer3" name="choices[]"
                                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Choice 3">
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="correct_answer" value="3" class="mr-2">
                                    <input type="text" id="answer4" name="choices[]"
                                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Choice 4">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between space-x-4">
                            <button type="button" onclick="backStep()"
                                    class="px-4 py-2 border border-gray-300 font-medium rounded-lg hover:bg-gray-50 transition">
                                Back
                            </button>
                            <button type="button" onclick="addQuestion()"
                                    class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                                Add Question
                            </button>
                            <button type="button" onclick="nextStep()"
                                    class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                                Next
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Review & Publish -->
                    <div id="step3" class="hidden space-y-6">
                        <h2 class="text-xl font-semibold text-center">Review Your Quiz</h2>
                        <div id="preview" class="p-4 bg-gray-50 rounded-lg border border-gray-200"></div>

                        <div class="flex justify-between">
                            <button type="button" onclick="backStep()"
                                    class="px-4 py-2 border border-gray-300 font-medium rounded-lg hover:bg-gray-50 transition">
                                Back
                            </button>
                            <div class="space-x-4">
                                <button type="submit" name="action" value="save"
                                        class="px-4 py-2 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 transition">
                                    Save Draft
                                </button>
                                <button type="submit" name="action" value="publish"
                                        class="px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                                    Publish Quiz
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        let currentQuestionIndex = 0;
        let quizData = [];
        let totalQuestions = 0;

        // Step navigation
        function nextStep() {
            if (currentStep === 1) {
                const total = document.getElementById("totalQuestions").value;
                if (!total || total < 1) {
                    alert("Please enter the total number of questions.");
                    return;
                }
                totalQuestions = parseInt(total);
                document.getElementById("step1").classList.add("hidden");
                document.getElementById("step2").classList.remove("hidden");
                updateStepIndicator(2);
                currentStep = 2;
                currentQuestionIndex = 0;
                loadQuestionData(); // Load question data when moving to step 2
                updateQuestionCountDisplay();
                return;
            }

            if (currentStep === 2) {
                if (!saveCurrentQuestion()) {
                    return; // Don't proceed if validation fails
                }

                if (currentQuestionIndex < totalQuestions - 1) {
                    currentQuestionIndex++;
                    loadQuestionData(); // Load next question's data
                    updateQuestionCountDisplay();
                } else {
                    document.getElementById("step2").classList.add("hidden");
                    document.getElementById("step3").classList.remove("hidden");
                    updateStepIndicator(3);
                    currentStep = 3;
                    updatePreview();
                }
            }
        }

        function backStep() {
            if (currentStep === 3) {
                document.getElementById("step3").classList.add("hidden");
                document.getElementById("step2").classList.remove("hidden");
                updateStepIndicator(2);
                currentStep = 2;
                currentQuestionIndex = totalQuestions - 1;
                loadQuestionData(); // Load the last question's data
                updateQuestionCountDisplay();
                return;
            }

            if (currentStep === 2) {
                if (currentQuestionIndex > 0) {
                    saveCurrentQuestion(); // Save current question before moving back
                    currentQuestionIndex--;
                    loadQuestionData(); // Load previous question's data
                    updateQuestionCountDisplay();
                } else {
                    document.getElementById("step2").classList.add("hidden");
                    document.getElementById("step1").classList.remove("hidden");
                    updateStepIndicator(1);
                    currentStep = 1;
                }
            }
        }

        function updateStepIndicator(step) {
            document.querySelectorAll('.step').forEach((el, index) => {
                el.classList.toggle('active', index + 1 === step);
            });
        }

        function saveCurrentQuestion() {
            const question = document.getElementById("question").value;
            const choices = [
                document.getElementById("answer1").value,
                document.getElementById("answer2").value,
                document.getElementById("answer3").value,
                document.getElementById("answer4").value
            ];
            const correctAnswer = document.querySelector('input[name="correct_answer"]:checked')?.value || 0;

            if (question.trim() === "" || choices.some(choice => choice.trim() === "")) {
                alert("Please fill in all fields before saving the question.");
                return false;
            }

            quizData[currentQuestionIndex] = { 
                question, 
                choices, 
                correct_answer: parseInt(correctAnswer) 
            };
            
            return true;
        }

        function loadQuestionData() {
            // Clear all fields first
            document.getElementById("question").value = "";
            document.getElementById("answer1").value = "";
            document.getElementById("answer2").value = "";
            document.getElementById("answer3").value = "";
            document.getElementById("answer4").value = "";
            document.querySelectorAll('input[name="correct_answer"]').forEach(radio => {
                radio.checked = false;
            });
            
            // Load data if it exists
            if (quizData[currentQuestionIndex]) {
                const q = quizData[currentQuestionIndex];
                document.getElementById("question").value = q.question;
                document.getElementById("answer1").value = q.choices[0] || "";
                document.getElementById("answer2").value = q.choices[1] || "";
                document.getElementById("answer3").value = q.choices[2] || "";
                document.getElementById("answer4").value = q.choices[3] || "";
                
                // Set the correct answer radio button
                const correctIndex = q.correct_answer || 0;
                document.querySelectorAll('input[name="correct_answer"]')[correctIndex].checked = true;
            } else {
                // Set first radio button as default if no data exists
                document.querySelectorAll('input[name="correct_answer"]')[0].checked = true;
            }
        }

        function updateQuestionCountDisplay() {
            const tracker = document.getElementById("questionTracker");
            const savedCount = quizData.filter(q => q).length;
            const total = totalQuestions;
            
            // Update the display text
            if (currentQuestionIndex >= savedCount) {
                // We're on a new question that hasn't been saved yet
                tracker.textContent = `${savedCount} / ${total} questions added (currently editing new question ${currentQuestionIndex + 1})`;
            } else {
                // We're editing an existing question
                tracker.textContent = `${savedCount} / ${total} questions added (currently editing Q${currentQuestionIndex + 1})`;
            }
        }

        function updatePreview() {
            const previewDiv = document.getElementById("preview");
            previewDiv.innerHTML = "";

            const title = document.getElementById("title").value;
            const desc = document.getElementById("description").value;

            previewDiv.innerHTML += `
                <div class="quiz-meta mb-6">
                    <h2 class="text-xl font-bold mb-2">${title}</h2>
                    <p class="text-gray-600">${desc}</p>
                </div>
            `;

            quizData.forEach((q, index) => {
                let questionHTML = `
                    <div class="question-card mb-4">
                        <h3 class="font-semibold mb-2">Question ${index + 1}</h3>
                        <p class="question-text mb-3">${q.question}</p>
                        <ul class="choices space-y-2">
                            ${q.choices.map((choice, i) => `
                                <li class="${i === q.correct_answer ? 'bg-blue-50 border-blue-200' : ''}">
                                    ${choice} ${i === q.correct_answer ? '<span class="text-blue-600 ml-2">(Correct Answer)</span>' : ''}
                                </li>
                            `).join("")}
                        </ul>
                    </div>
                `;
                previewDiv.innerHTML += questionHTML;
            });

            // Clear any existing hidden inputs first
            const existingInputs = previewDiv.querySelectorAll('input[type="hidden"]');
            existingInputs.forEach(input => input.remove());

            // Prepare hidden inputs for form submission
            quizData.forEach((q, index) => {
                const questionInput = document.createElement('input');
                questionInput.type = 'hidden';
                questionInput.name = `questions[${index}][question]`;
                questionInput.value = q.question;
                previewDiv.appendChild(questionInput);

                q.choices.forEach((choice, i) => {
                    const choiceInput = document.createElement('input');
                    choiceInput.type = 'hidden';
                    choiceInput.name = `questions[${index}][choices][${i}]`;
                    choiceInput.value = choice;
                    previewDiv.appendChild(choiceInput);
                });

                const correctInput = document.createElement('input');
                correctInput.type = 'hidden';
                correctInput.name = `questions[${index}][correct_answer]`;
                correctInput.value = q.correct_answer;
                previewDiv.appendChild(correctInput);
            });
        }

        // Update the addQuestion function to use saveCurrentQuestion
        function addQuestion() {
            if (saveCurrentQuestion()) {
                if (currentQuestionIndex < totalQuestions - 1) {
                    currentQuestionIndex++;
                    loadQuestionData();
                    updateQuestionCountDisplay();
                } else {
                    document.getElementById("step2").classList.add("hidden");
                    document.getElementById("step3").classList.remove("hidden");
                    updateStepIndicator(3);
                    currentStep = 3;
                    updatePreview();
                }
            }
        }

        // Initialize dropdown toggle
        document.getElementById('profileDropdownBtn').addEventListener('click', function() {
            document.getElementById('profileDropdown').classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
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