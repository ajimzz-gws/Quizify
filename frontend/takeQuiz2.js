// Get quiz ID from URL
const quizId = new URLSearchParams(window.location.search).get("id");

// Get DOM elements
const quizTitle = document.getElementById("quiz-title");
const quizDesc = document.getElementById("quiz-desc");
const quizForm = document.getElementById("quiz-form");
const resultDiv = document.getElementById("result");
const submitBtn = document.getElementById("submit-btn");

// Fetch quiz data from backend
fetch(`http://localhost:8000/api/quizzes/${quizId}`)
  .then(res => res.json())
  .then(data => {
    if (!data || !data.questions || data.questions.length === 0) {
      quizTitle.textContent = "Quiz not found.";
      return;
    }

    // Set quiz title and description
    quizTitle.textContent = data.title || "Untitled Quiz";
    quizDesc.textContent = data.description || "";

    // Render each question
    data.questions.forEach((q, index) => {
      const block = document.createElement("div");
      block.className = "question-block";

      const question = document.createElement("h3");
      question.textContent = `${index + 1}. ${q.question}`;
      block.appendChild(question);

      const choicesDiv = document.createElement("div");
      choicesDiv.className = "choices";

      q.choices.forEach(choice => {
        const label = document.createElement("label");
        label.style.display = "block";
        const input = document.createElement("input");
        input.type = "radio";
        input.name = `q${index}`;
        input.value = choice;
        label.appendChild(input);
        label.appendChild(document.createTextNode(" " + choice));
        choicesDiv.appendChild(label);
      });

      block.appendChild(choicesDiv);
      quizForm.appendChild(block);
    });

    // Show submit button after quiz is loaded
    submitBtn.classList.remove("hidden");
  })
  .catch(err => {
    quizTitle.textContent = "Error loading quiz.";
    console.error("Fetch error:", err);
  });

// Handle quiz submission
submitBtn.addEventListener("click", function (e) {
  e.preventDefault();

  const total = quizForm.querySelectorAll(".question-block").length;
  let answered = 0;

  for (let i = 0; i < total; i++) {
    const selected = quizForm.querySelector(`input[name="q${i}"]:checked`);
    if (selected) answered++;
  }

  resultDiv.classList.remove("hidden");
  resultDiv.textContent = `You answered ${answered} out of ${total} questions.`;

  // Optional: send result to backend here
});
