let currentStep = 1;
let quizData = [];

function nextStep() {
    document.getElementById(`step${currentStep}`).classList.add("hidden");
    currentStep++;
    document.getElementById(`step${currentStep}`).classList.remove("hidden");

    // Highlight step indicator
    document.querySelectorAll(".step").forEach((step, index) => {
        step.classList.toggle("active", index === currentStep - 1);
    });
}

function addQuestion() {
    let question = document.getElementById("question").value;
    let choices = [
        document.getElementById("answer1").value,
        document.getElementById("answer2").value,
        document.getElementById("answer3").value,
        document.getElementById("answer4").value
    ];

    if (question.trim() === "" || choices.some(choice => choice.trim() === "")) {
        alert("Please fill in all fields before adding a question.");
        return;
    }

    quizData.push({ question, choices });

    document.getElementById("question").value = "";
    document.getElementById("answer1").value = "";
    document.getElementById("answer2").value = "";
    document.getElementById("answer3").value = "";
    document.getElementById("answer4").value = "";

    updatePreview();
}

function updatePreview() {
    let previewDiv = document.getElementById("preview");
    previewDiv.innerHTML = ""; // Clear previous preview

    quizData.forEach((q, index) => {
        let questionHTML = `<p><strong>${index + 1}. ${q.question}</strong></p>`;
        q.choices.forEach(choice => {
            questionHTML += `<p>- ${choice}</p>`;
        });
        previewDiv.innerHTML += questionHTML;
    });
}

function publishQuiz() {
    if (quizData.length === 0) {
        alert("You need to add at least one question before publishing!");
        return;
    }

    // Store quiz data in local storage
    localStorage.setItem("quizData", JSON.stringify(quizData));

    // Provide feedback to user
    alert("Quiz published successfully!");

    // Redirect to published quiz page (change "published.html" to the actual page)
    window.location.href = "published.html";

    console.log("Quiz data saved:", quizData); // Debugging
}