const quizId = new URLSearchParams(window.location.search).get("id");
const quizTitle = document.getElementById("quiz-title");
const quizDesc = document.getElementById("quiz-desc");
const quizForm = document.getElementById("quiz-form");
const resultDiv = document.getElementById("result");

fetch(`http://localhost:8000/api/quizzes/${quizId}`)
  .then(res => res.json())
  .then(data => {
    if (!data || !data.questions) {
      quizTitle.textContent = "Quiz not found.";
      return;
    }

    quizTitle.textContent = data.title;
    quizDesc.textContent = data.description;

    data.questions.forEach((q, index) => {
      const block = document.createElement("div");
      block.className = "question-block";

      const question = document.createElement("h3");
      question.textContent = `${index + 1}. ${q.question}`;
      block.appendChild(question);

      const choicesDiv = document.createElement("div");
      choicesDiv.className = "choices";

      q.choices.forEach((choice, i) => {
        const label = document.createElement("label");
        const input = document.createElement("input");
        input.type = "radio";
        input.name = `q${index}`;
        input.value = choice;
        label.appendChild(input);
        label.appendChild(document.createTextNode(choice));
        choicesDiv.appendChild(label);
      });

      block.appendChild(choicesDiv);
      quizForm.appendChild(block);
    });
  })
  .catch(err => {
    quizTitle.textContent = "Error loading quiz.";
    console.error(err);
  });

document.getElementById("submit-btn").addEventListener("click", function () {
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
