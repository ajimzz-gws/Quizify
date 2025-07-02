<?php
require_once __DIR__ . '/../lib/auth.php';
require_login();
require_once __DIR__ . '/../config/db_connect.php';

$userId = $_SESSION['user_id'];
$role   = $_SESSION['role'];
$name   = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head> … </head>
<body>
  <h1>Welcome, <?= htmlspecialchars($name) ?></h1>
  <a href="../api/logout.php">Logout</a>

  <?php if ($role === 'teacher'): ?>
    <h2>Your Quizzes</h2>
    <?php
    $stmt = $conn->prepare("SELECT id, title FROM quizzes WHERE created_by = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $quizzes = $stmt->get_result();
    ?>
    <ul>
      <?php while ($q = $quizzes->fetch_assoc()): ?>
        <li>
          <?= htmlspecialchars($q['title']) ?>
          (<a href="view_attempts.php?quiz=<?= $q['id'] ?>">View Attempts</a>)
        </li>
      <?php endwhile; ?>
    </ul>

  <?php else: /* student */ ?>
    <h2>Available Quizzes</h2>
    <?php
    $result = $conn->query("SELECT id, title FROM quizzes");
    ?>
    <ul>
      <?php while ($q = $result->fetch_assoc()): ?>
        <li>
          <a href="take_quiz.php?id=<?= $q['id'] ?>">
            <?= htmlspecialchars($q['title']) ?>
          </a>
        </li>
      <?php endwhile; ?>
    </ul>
  <?php endif; ?>
</body>
</html>