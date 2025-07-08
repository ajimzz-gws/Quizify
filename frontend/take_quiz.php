<?php
require_once 'bootstrap.php';
$auth->requireLogin();

$quizId = $_GET['id'] ?? null;
$quiz = $db->get('quizzes', $quizId);

if (!$quiz) {
    header("Location: quiz_not_found.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questions = json_decode($quiz['questions_json'], true);
    $score = calculateScore($_POST['answers'], $questions);
    
    $attemptData = [
        'user_id' => $_SESSION['user_id'],
        'quiz_id' => $quizId,
        'score' => $score,
        'answers_json' => json_encode($_POST['answers']),
        'completed_at' => date('Y-m-d H:i:s')
    ];
    
    $db->insert('quiz_attempts', $attemptData);
    $attempt = $db->pdo->query("
        SELECT id FROM quiz_attempts 
        WHERE user_id = {$_SESSION['user_id']} AND quiz_id = $quizId
        ORDER BY completed_at DESC LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);
    header("Location: quiz_result.php?attempt_id=" . $attempt['id']);
    exit;
}

function calculateScore($userAnswers, $questions) {
    $correct = 0;
    foreach ($questions as $i => $q) {
        if (isset($userAnswers[$i]) && $userAnswers[$i] == $q['correct_answer']) {
            $correct++;
        }
    }
    return round(($correct / count($questions)) * 100);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Take Quiz - <?= htmlspecialchars($quiz['title']) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .choices label {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
      padding: 10px 14px;
      background: #f0f9ff;
      border: 1px solid #b3e5fc;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s ease;
      font-size: 1rem;
    }
    .choices input[type="radio"] {
      margin-right: 12px;
      accent-color: #0288d1;
      transform: scale(1.1);
    }
    .choices label:hover {
      background: #e0f7fa;
      transform: scale(1.02);
    }
    .choices input[type="radio"]:checked + span {
      background-color: #b3e5fc;
      font-weight: bold;
      padding: 4px 8px;
      border-radius: 4px;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body class="bg-blue-50">
    <script>
    // Start timer with optional time limit
    const timeLimit = <?= json_encode($quiz['time_limit'] ?? null) ?>; // in minutes
    let timeLeft = timeLimit ? timeLimit * 60 : null;
    let quizEndTime = timeLimit ? new Date().getTime() + timeLimit * 60000 : null;

    function updateTimer() {
        const timerElement = document.getElementById('time-display');
        
        if (timeLimit) {
            const now = new Date().getTime();
            const distance = quizEndTime - now;
            
            if (distance <= 0) {
                clearInterval(timerInterval);
                alert("Time's up! Submitting your quiz...");
                document.getElementById('quiz-form').submit();
                return;
            }
            
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            // Visual warning when 5 minutes left
            if (distance < 300000 && !document.querySelector('.time-warning')) {
                const warning = document.createElement('div');
                warning.className = 'time-warning bg-yellow-100 p-2 rounded mb-4';
                warning.textContent = '⚠️ Less than 5 minutes remaining!';
                timerElement.parentNode.appendChild(warning);
            }
        } else {
            // Unlimited time - just show elapsed time
            seconds++;
            timerElement.textContent = formatTime(seconds);
        }
    }

    const timerInterval = setInterval(updateTimer, 1000);
    </script>
    <?php if (!empty($quiz['time_limit'])): ?>
        <div class="fixed top-4 right-4 bg-white p-2 rounded-lg shadow-md">
            <span class="font-semibold">Time Remaining:</span>
            <span id="timer-display" class="ml-2"><?= $quiz['time_limit'] ?>:00</span>
        </div>
    <?php endif; ?>
  <header class="bg-blue-700 text-white py-5 shadow-md">
    <div class="container mx-auto px-4">
      <h1 class="text-2xl font-bold" id="quiz-title"><?= htmlspecialchars($quiz['title']) ?></h1>
      <?php if (!empty($quiz['description'])): ?>
        <p class="mt-2" id="quiz-desc"><?= htmlspecialchars($quiz['description']) ?></p>
      <?php endif; ?>
    </div>
    <div class="flex justify-between mt-8">
        <a href="quiz_library.php" 
           class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors">
           <i class="fas fa-arrow-left mr-2"></i> Cancel Quiz
        </a>
  </header>

  <main class="container mx-auto px-4 py-8">
    <form method="post" class="quiz-container bg-white rounded-lg shadow-md overflow-hidden p-6 max-w-3xl mx-auto">
      <?php foreach (json_decode($quiz['questions_json'], true) as $i => $q): ?>
        <div class="question-block mb-8">
          <h3 class="text-xl font-semibold text-blue-800 mb-4">
            <?= ($i+1) ?>. <?= htmlspecialchars($q['question']) ?>
          </h3>
          
          <div class="choices">
            <?php foreach ($q['choices'] as $j => $choice): ?>
              <label class="mb-3">
                <input type="radio" name="answers[<?= $i ?>]" value="<?= $j ?>" required>
                <span><?= htmlspecialchars($choice) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>


      <div class="flex justify-between mt-8">
        <a href="quiz_library.php" 
           class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors">
           <i class="fas fa-arrow-left mr-2"></i> Cancel Quiz
        </a>
      </div>
      <button type="submit" class="w-full md:w-auto px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-lg font-medium">
        Submit Quiz
      </button>
    </form>

    <script>
    // Back button confirmation
    document.querySelector('a[href="quiz_library.php"]').addEventListener('click', function(e) {
        if(Array.from(document.querySelectorAll('input[type="radio"]:checked')).length > 0) {
            if(!confirm('Are you sure you want to cancel? Your answers will be lost.')) {
                e.preventDefault();
            }
        }
    });

    // Page leave warning
    window.addEventListener('beforeunload', function(e) {
        if(Array.from(document.querySelectorAll('input[type="radio"]:checked')).length > 0) {
            e.preventDefault();
            e.returnValue = 'You have unsaved answers. Are you sure you want to leave?';
        }
    });
    </script>
  </main>
</body>
</html>