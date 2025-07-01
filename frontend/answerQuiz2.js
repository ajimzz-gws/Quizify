// Simulasi: Dapatkan ID kuiz dari URL, contoh: answer-quiz.html?quiz_id=1
const queryParams = new URLSearchParams(window.location.search);
const quizId = queryParams.get("quiz_id");

let quizData = [];
let score = 0;

// Muat soalan kuiz dari backend
async function fetchQuiz() {
  try {
    const res = await fetch(`/api/quiz/${quizId}`);
    const data = await res.json();
    quizData = data.questions;

    document.getElementById("quiz-title").textContent = data.title;

    const container = document.getElementById("question-container");
    container.innerHTML = "";

    quizData.forEach((q, i) => {
      const qDiv = document.createElement("div");
      qDiv.className = "question";

      qDiv.innerHTML = `<p><strong>${i + 1}. ${q.question}</strong></p>`;
      q.options.forEach(opt => {
        qDiv.innerHTML += `
          <label>
            <input type="radio" name="q${q.id}" value="${opt}"> ${opt}
          </label>
        `;
      });

      container.appendChild(qDiv);
    });
  } catch (err) {
    document.querySelector(".quiz-box").innerHTML = "<p>❌ Failed to load quiz.</p>";
  }
}

// Hantar jawapan
document.getElementById("quiz-form").addEventListener("submit", async function (e) {
  e.preventDefault();

  const answers = [];
  quizData.forEach(q => {
    const selected = document.querySelector(`input[name="q${q.id}"]:checked`);
    answers.push({
      question_id: q.id,
      answer: selected ? selected.value : null
    });
  });

  const res = await fetch("/api/submit-quiz", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ quiz_id: quizId, answers: answers })
  });

  const result = await res.json();

  // Papar keputusan
  document.querySelector(".quiz-box").innerHTML = `
    <h2>Quiz Completed!</h2>
    <p>Your Score: ${result.score} / ${quizData.length}</p>
  `;
});

// Mula bila load
fetchQuiz();
