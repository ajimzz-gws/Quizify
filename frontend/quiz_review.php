<?php
require_once 'bootstrap.php';

$auth->requireRole('student');

$attemptId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];

// Fetch the attempt with quiz info
$attempt = $db->pdo->query("
  SELECT qa.*, q.title, q.questions_json
  FROM quiz_attempts qa
  JOIN quizzes q ON qa.quiz_id = q.id
  WHERE qa.id = $attemptId AND qa.user_id = $userId
")->fetch(PDO::FETCH_ASSOC);

if (!$attempt) {
  $_SESSION['error'] = "Quiz attempt not found or access denied.";
  header("Location: dashboard_student.php");
  exit;
}

$questions = json_decode($attempt['questions_json'], true) ?? [];
$answers = json_decode($attempt['answers_json'], true) ?? [];
$totalQuestions = count($questions);
$correctCount = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Review: <?= htmlspecialchars($attempt['title']) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#0288d1',
            soft: '#b3e5fc'
          },
          fontFamily: {
            body: ['Segoe UI', 'Tahoma', 'sans-serif']
          }
        }
      }
    }
  </script>
</head>
<body class="font-body bg-soft text-gray-800">

  <!-- Header -->
  <header class="fixed top-0 left-0 w-full h-20 bg-primary text-white flex justify-between items-center px-6 shadow-md z-50">
    <div class="flex items-center gap-3">
      <img src="images\quzify4.png" alt="Logo" class="h-10 object-contain">
      <span class="text-2xl font-bold">Quiz Review</span>
    </div>
    <nav>
      <ul class="flex gap-6 font-semibold text-base">
        <li><a href="dashboard_student.php" class="hover:text-soft">Dashboard</a></li>
        <li><a href="quiz_library.php" class="hover:text-soft">Library</a></li>
      </ul>
    </nav>
  </header>

  <!-- Main -->
  <main class="pt-28 pb-16 px-4">
    <section class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-lg text-center">
      <h1 class="text-3xl md:text-4xl font-bold text-primary mb-3"><?= htmlspecialchars($attempt['title']) ?></h1>
      <p class="text-lg mb-1">Your Score: <span class="font-semibold text-primary"><?= $attempt['score'] ?>%</span></p>
      <p class="text-sm text-gray-500 mb-6"><?= $totalQuestions ?> Questions</p>

      <!-- Review Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
        <?php foreach ($questions as $index => $question): 
          $correct = (int)$question['correct_answer'];
          $selected = isset($answers[$index]) ? (int)$answers[$index] : null;
          $isCorrect = $selected === $correct;
          if ($isCorrect) $correctCount++;
        ?>
        <div class="bg-white p-5 rounded-lg shadow-md border-l-4 <?= $isCorrect ? 'border-green-500' : 'border-red-500' ?> hover:shadow-lg transition">
          <h3 class="text-lg font-semibold text-primary mb-2">Q<?= $index + 1 ?>: <?= htmlspecialchars($question['question']) ?></h3>
          <p><strong>Your Answer:</strong>
            <?= isset($question['choices'][$selected]) 
              ? htmlspecialchars($question['choices'][$selected]) 
              : '<em>Not answered</em>' ?>
          </p>
          <p><strong>Correct Answer:</strong> <?= htmlspecialchars($question['choices'][$correct]) ?></p>
          <p class="<?= $isCorrect ? 'text-green-600' : 'text-red-600 italic' ?> mt-2 font-medium">
            <?= $isCorrect ? '✔️ Correct' : '❌ Incorrect — review this concept' ?>
          </p>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Buttons -->
      <div class="flex flex-wrap justify-center gap-4 mt-10">
        <a href="dashboard_student.php">
          <button class="bg-primary hover:bg-sky-700 text-white px-5 py-2 rounded-lg shadow transition">🏠 Back to Dashboard</button>
        </a>
        <a href="quiz_library.php">
          <button class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg shadow transition">🗂 Explore More Quizzes</button>
        </a>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="bg-primary text-white text-center py-6 shadow-inner">
    <p class="text-sm">📘 Reflect, revisit, improve — every review grows your skills.</p>
  </footer>

</body>
</html>