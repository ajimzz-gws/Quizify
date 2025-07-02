let currentStep = 1;
let currentQuestionIndex = 0;
let quizData = [];
let totalQuestions = 0;

// Handle step switching
function nextStep() {
  if (currentStep === 1) {
    const total = document.getElementById("totalQuestions").value;
    if (!total || total < 1) {
      alert("Please enter the total number of questions.");
      return;
    }
    totalQuestions = parseInt(total);
    localStorage.setItem("totalQuestions", total);
    document.getElementById("step1").classList.add("hidden");
    document.getElementById("step2").classList.remove("hidden");
    currentStep = 2;
    currentQuestionIndex = 0;
    loadQuestion(currentQuestionIndex);
    updateQuestionCountDisplay();
    return;
  }

  // Step 2: Navigate forward through questions
  if (currentStep === 2) {
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
    alert("Please fill in all fields before adding a question.");
    return;
  }

  // Update current index or add new
  quizData[currentQuestionIndex] = { question, choices };
  updateQuestionCountDisplay();
  alert(`Question ${currentQuestionIndex + 1} saved`);
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
  const total = totalQuestions || localStorage.getItem("totalQuestions") || "?";
  tracker.textContent = `${quizData.filter(q => q).length} / ${total} questions added (currently editing Q${currentQuestionIndex + 1})`;
}

function saveToLocal() {
  localStorage.setItem("quizData", JSON.stringify(quizData));
  alert("Quiz saved locally!");
}

function updatePreview() {
  let previewDiv = document.getElementById("preview");
  previewDiv.innerHTML = "";

  quizData.forEach((q, index) => {
    let questionHTML = `<p><strong>${index + 1}. ${q.question}</strong></p>`;
    q.choices.forEach(choice => {
      questionHTML += `<p>- ${choice}</p>`;
    });
    previewDiv.innerHTML += questionHTML;
  });

  updateQuestionCountDisplay();
}

function publishQuiz() {
  if (quizData.length === 0) {
    alert("You need to add at least one question before publishing!");
    return;
  }

  localStorage.setItem("quizData", JSON.stringify(quizData));
  alert("Quiz published successfully!");
  window.location.href = "published.html";
}