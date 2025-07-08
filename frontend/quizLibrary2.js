// DOM element where quizzes will be displayed
const quizList = document.getElementById("quiz-list");

// ✅ Replace this with your deployed backend URL when ready
const API_URL = "http://localhost:8000/api/quizzes";

// Fetch quizzes from backend
fetch(API_URL)
  .then(response => {
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
  })
  .then(data => {
    // Check if data is a valid array
    if (!Array.isArray(data) || data.length === 0) {
      quizList.innerHTML = "<p>No quizzes available at the moment.</p>";
      return;
    }

    // Loop through each quiz and create a card
    data.forEach(quiz => {
      const card = document.createElement("div");
      card.className = "quiz-card";

      card.innerHTML = `
        <h3>${quiz.title}</h3>
        <p>${quiz.description || "No description provided."}</p>
        <a href="takeQuiz.html?id=${quiz._id}">Take Quiz</a>
      `;

      quizList.appendChild(card);
    });
  })
  .catch(error => {
    console.error("Error fetching quizzes:", error);
    quizList.innerHTML = "<p>Failed to load quizzes. Please try again later.</p>";
  });
