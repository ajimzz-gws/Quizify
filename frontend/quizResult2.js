// ✅ Load quiz result when page is ready
window.onload = async function () {
  const params = new URLSearchParams(window.location.search);
  const attemptId = params.get("attempt_id");

  if (!attemptId) {
    document.getElementById("summary").textContent = "❌ No result found.";
    return;
  }

  try {
    const res = await fetch(`http://localhost:8000/api/quiz-result/${attemptId}`);
    const data = await res.json();

    const total = data.total_questions;
    const correct = data.correct_answers;
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

  } catch (err) {
    console.error("Failed to load result:", err);
    document.getElementById("summary").textContent = "❌ Error loading result.";
  }
};

// ✅ Activate header buttons after DOM is ready
document.addEventListener("DOMContentLoaded", function () {
  const dashboardBtn = document.getElementById("dashboard-btn");
  const logoutBtn = document.getElementById("logout-btn");

  if (dashboardBtn) {
    dashboardBtn.addEventListener("click", function (e) {
      e.preventDefault();
      window.location.href = "dashboard.html";
    });
  }

  if (logoutBtn) {
    logoutBtn.addEventListener("click", function (e) {
      e.preventDefault();
      alert("You have been logged out.");
      window.location.href = "login.html";
    });
  }
});

// ✅ Retake Quiz button
function retakeQuiz() {
  window.location.href = "quiz.html";
}

// ✅ Review Answers button
function reviewAnswers() {
  const params = new URLSearchParams(window.location.search);
  const attemptId = params.get("attempt_id");
  window.location.href = `review.html?attempt_id=${attemptId}`;
}
