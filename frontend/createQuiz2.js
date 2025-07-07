let currentStep = 1;
let currentQuestionIndex = 0;
let quizData = [];
let totalQuestions = 0;

// Step navigation
function nextStep() {
  if (currentStep === 1) {
    const total = document.getElementById("totalQuestions").value;
    if (!total || total < 1) {
      alert("Please enter the total number of questions.");
      return;
    }
    totalQuestions = parseInt(total);
    document.getElementById("step1").classList.add("hidden");
    document.getElementById("step2").classList.remove("hidden");
    currentStep = 2;
    currentQuestionIndex = 0;
    loadQuestion(currentQuestionIndex);
    updateQuestionCountDisplay();
    return;
  }

  if (currentStep === 2) {
    addQuestion();

    if (currentQuestionIndex < totalQuestions - 1) {
      currentQuestionIndex++;
      loadQuestion(currentQuestionIndex);
      updateQuestionCountDisplay();
    } else {
      document.getElementById("step2").classList.add("hidden");
      document.getElementById("step3").classList.remove("hidden");
      currentStep = 3;
      updatePreview();
    }
  }
}

function backStep() {
  if (currentStep === 3) {
    document.getElementById("step3").classList.add("hidden");
    document.getElementById("step2").classList.remove("hidden");
    currentStep = 2;
    currentQuestionIndex = totalQuestions - 1;
    loadQuestion(currentQuestionIndex);
    updateQuestionCountDisplay();
    return;
  }

  if (currentStep === 2) {
    if (currentQuestionIndex > 0) {
      currentQuestionIndex--;
      loadQuestion(currentQuestionIndex);
      updateQuestionCountDisplay();
    } else {
      document.getElementById("step2").classList.add("hidden");
      document.getElementById("step1").classList.remove("hidden");
      currentStep = 1;
    }
  }
}

function addQuestion() {
  const question = document.getElementById("question").value;
  const choices = [
    document.getElementById("answer1").value,
    document.getElementById("answer2").value,
    document.getElementById("answer3").value,
    document.getElementById("answer4").value
  ];

  if (question.trim() === "" || choices.some(choice => choice.trim() === "")) {
    alert("Please fill in all fields before saving the question.");
    return;
  }

  quizData[currentQuestionIndex] = { question, choices };
  updateQuestionCountDisplay();
}

function loadQuestion(index) {
  const q = quizData[index] || { question: "", choices: ["", "", "", ""] };
  document.getElementById("question").value = q.question;
  document.getElementById("answer1").value = q.choices[0];
  document.getElementById("answer2").value = q.choices[1];
  document.getElementById("answer3").value = q.choices[2];
  document.getElementById("answer4").value = q.choices[3];
}

function updateQuestionCountDisplay() {
  const tracker = document.getElementById("questionTracker");
  tracker.textContent = `${quizData.filter(q => q).length} / ${totalQuestions} questions added (currently editing Q${currentQuestionIndex + 1})`;
}

function updatePreview() {
  const previewDiv = document.getElementById("preview");
  previewDiv.innerHTML = "";

  const title = document.getElementById("quiz-title").value;
  const desc = document.getElementById("quiz-desc").value;

  previewDiv.innerHTML += `
    <div class="quiz-meta">
      <h2>${title}</h2>
      <p>${desc}</p>
    </div>
  `;

  quizData.forEach((q, index) => {
    let questionHTML = `
      <div class="question-card">
        <h3>Question ${index + 1}</h3>
        <p class="question-text">${q.question}</p>
        <ul class="choices">
          ${q.choices.map(choice => `<li>${choice}</li>`).join("")}
        </ul>
      </div>
    `;
    previewDiv.innerHTML += questionHTML;
  });

  updateQuestionCountDisplay();
}

// ✅ Save button: sends quiz to backend as a draft
function saveQuiz() {
  const title = document.getElementById("quiz-title").value;
  const description = document.getElementById("quiz-desc").value;

  if (!title || quizData.length === 0) {
    alert("Please enter a title and at least one question before saving.");
    return;
  }

  fetch("http://localhost:8000/api/quizzes", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "Accept": "application/json"
    },
    body: JSON.stringify({
      title,
      description,
      questions: quizData,
      status: "draft"
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert("Quiz saved as draft!");
    } else {
      alert("Failed to save quiz: " + (data.message || "Unknown error"));
    }
  })
  .catch(err => {
    console.error(err);
    alert("Something went wrong while saving the quiz.");
  });
}

// ✅ Publish button: sends quiz to backend as published
function publishQuiz() {
  if (quizData.length < totalQuestions) {
    alert(`You need to complete all ${totalQuestions} questions before publishing!`);
    return;
  }

  const title = document.getElementById("quiz-title").value;
  const description = document.getElementById("quiz-desc").value;

  fetch("http://localhost:8000/api/quizzes", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "Accept": "application/json"
    },
    body: JSON.stringify({
      title,
      description,
      questions: quizData,
      status: "published"
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert("Quiz published and saved to database!");
      window.location.href = "published.html";
    } else {
      alert("Failed to publish quiz: " + (data.message || "Unknown error"));
    }
  })
  .catch(err => {
    console.error(err);
    alert("Something went wrong while publishing the quiz.");
  });
}
