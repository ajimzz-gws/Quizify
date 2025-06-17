window.onload = function () {
  // Simulate receiving quiz result from localStorage or URL params
  const total = localStorage.getItem("totalQuestions") || 20;
  const correct = localStorage.getItem("correctAnswers") || 17;

  const scorePercent = Math.round((correct / total) * 100);
  document.getElementById("percentage").textContent = `${scorePercent} %`;
  document.getElementById("summary").textContent = `You scored ${correct} out of ${total}`;

  let feedback = "";
  if (scorePercent === 100) {
    feedback = "🌟 Perfect! You've mastered this quiz.";
  } else if (scorePercent >= 80) {
    feedback = "🎉 Great job! You have a solid understanding.";
  } else if (scorePercent >= 50) {
    feedback = "👍 Good attempt! Consider reviewing a few topics.";
  } else {
    feedback = "💡 Keep practicing. You'll get better!";
  }

  document.getElementById("feedback").textContent = feedback;
};

function retakeQuiz() {
  window.location.href = "quiz.html"; // Redirect to quiz page
}

function reviewAnswers() {
  window.location.href = "review.html"; // Redirect to review page
}