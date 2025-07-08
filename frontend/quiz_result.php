<?php
require_once 'bootstrap.php';
$auth->requireLogin();

$attemptId = $_GET['attempt_id'] ?? null;
$attempt = $db->get('quiz_attempts', $attemptId);

if (!$attempt || $attempt['user_id'] !== $_SESSION['user_id']) {
    header("Location: access_denied.php");
    exit;
}

$quiz = $db->get('quizzes', $attempt['quiz_id']);
$questions = json_decode($quiz['questions_json'], true);
$userAnswers = json_decode($attempt['answers_json'], true);
$percentage = $attempt['score'];
$correctCount = round(count($questions) * ($percentage / 100));

// Determine feedback message
if ($percentage == 100) {
    $feedback = "🌟 Perfect! You've mastered this quiz.";
} elseif ($percentage >= 80) {
    $feedback = "🎉 Great job! You have a solid understanding.";
} elseif ($percentage >= 50) {
    $feedback = "👍 Good attempt! Consider reviewing a few topics.";
} else {
    $feedback = "💡 Keep practicing. You'll get better!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quiz Result - Quizify</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .question-review {
      background: white;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      border-left: 4px solid #0288d1;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .correct-answer {
      color: #10b981;
      font-weight: bold;
    }
    .incorrect-answer {
      color: #ef4444;
    }
  </style>
</head>
<body class="bg-sky-200 text-gray-800 font-sans overflow-x-hidden">

  <!-- Header -->
  <header class="bg-sky-600 text-white h-20 flex justify-between items-center px-8 fixed top-0 left-0 w-full z-50 shadow-md overflow-hidden">
    <div class="logo flex items-center gap-3">
      <img src="https://placehold.co/48x48/0288d1/ffffff?text=Q" alt="Quizify Logo" class="h-12 object-contain" />
      <span class="text-xl font-bold">Quizify</span>
    </div>
    <nav>
      <ul class="flex gap-6 text-sm font-semibold">
        <li><a href="dashboard_student.php" class="hover:text-sky-100 relative after:absolute after:bottom-0 after:left-0 after:w-0 after:h-0.5 after:bg-white hover:after:w-full after:transition-all">Dashboard</a></li>
        <li><a href="logout.php" class="hover:text-sky-100 relative after:absolute after:bottom-0 after:left-0 after:w-0 after:h-0.5 after:bg-white hover:after:w-full after:transition-all">Logout</a></li>
      </ul>
    </nav>
  </header>

  <!-- Main Content -->
  <main class="max-w-2xl mx-auto mt-36 mb-16 bg-white p-10 rounded-xl shadow-lg">
    <h1 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Quiz Result: <?= htmlspecialchars($quiz['title']) ?></h1>

    <!-- Score Summary -->
    <div class="score-box mb-8 text-center">
      <h2 class="text-5xl text-sky-600 font-bold mb-2"><?= $percentage ?>%</h2>
      <p class="text-lg text-gray-700">You scored <?= $correctCount ?> out of <?= count($questions) ?></p>
      <p class="text-base text-gray-600 mt-4"><?= $feedback ?></p>
    </div>

    <!-- Question Review -->
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Question Review</h2>
    <div class="space-y-4">
      <?php foreach ($questions as $i => $q): ?>
        <div class="question-review">
          <h3 class="text-lg font-medium text-gray-800 mb-2"><?= ($i+1) ?>. <?= htmlspecialchars($q['question']) ?></h3>
          
          <p class="mb-1 <?= ($userAnswers[$i] == $q['correct_answer']) ? 'correct-answer' : 'incorrect-answer' ?>">
            Your answer: <?= htmlspecialchars($q['choices'][$userAnswers[$i] ?? ''] ?? 'Not answered') ?>
          </p>
          
          <p class="correct-answer">
            Correct answer: <?= htmlspecialchars($q['choices'][$q['correct_answer']]) ?>
          </p>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Actions -->
    <div class="actions flex flex-wrap justify-center gap-4 mt-8">
      <a href="take_quiz.php?id=<?= $quiz['id'] ?>" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition">Retake Quiz</a>
      <a href="dashboard_student.php" class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 transition">Back to Dashboard</a>
    </div>
  </main>
</body>
</html>