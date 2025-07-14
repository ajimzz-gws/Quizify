<?php
require_once 'bootstrap.php';
$auth->requireLogin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Access Denied</title>
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
<body class="bg-soft font-body text-gray-800">

  <main class="flex items-center justify-center min-h-screen px-6">
    <section class="max-w-lg w-full bg-white rounded-xl shadow-lg p-8 text-center">
      <div class="mb-6">
        <img src="lock.png" alt="Access Denied" class="mx-auto h-20 mb-4">
        <h1 class="text-3xl font-bold text-red-600">Access Denied</h1>
        <p class="text-gray-700 mt-3">Sorry, you don't have permission to view this content.</p>
        <p class="text-sm text-gray-500 mt-1">It looks like you're trying to access a page or quiz result that isn’t yours or doesn't exist.</p>
      </div>

      <div class="flex justify-center gap-4 mt-6">
        <a href="dashboard_student.php">
          <button class="bg-primary hover:bg-sky-700 text-white px-5 py-2 rounded-md transition shadow">
            🏠 Back to Dashboard
          </button>
        </a>
        <a href="quiz_library.php">
          <button class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-md transition shadow">
            🗂 Browse Quizzes
          </button>
        </a>
      </div>
    </section>
  </main>

</body>
</html>