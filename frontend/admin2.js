document.addEventListener("DOMContentLoaded", () => {
  const quizzes = [
    { title: "Math Basics", createdBy: "Admin", attempts: 12 },
    { title: "Science Quiz", createdBy: "Teacher A", attempts: 8 },
    { title: "History Challenge", createdBy: "Teacher B", attempts: 5 }
  ];

  const tableBody = document.querySelector("#quizTable tbody");
  const searchInput = document.getElementById("searchInput");

  function renderTable(data) {
    tableBody.innerHTML = "";
    data.forEach((quiz, index) => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${quiz.title}</td>
        <td>${quiz.createdBy}</td>
        <td>${quiz.attempts}</td>
        <td class="actions">
          <button class="view" onclick="viewQuiz(${index})">View</button>
          <button class="edit" onclick="editQuiz(${index})">Edit</button>
          <button class="delete" onclick="deleteQuiz(${index})">Delete</button>
        </td>
      `;
      tableBody.appendChild(row);
    });
  }

  searchInput.addEventListener("input", () => {
    const keyword = searchInput.value.toLowerCase();
    const filtered = quizzes.filter(q =>
      q.title.toLowerCase().includes(keyword) ||
      q.createdBy.toLowerCase().includes(keyword)
    );
    renderTable(filtered);
  });

  renderTable(quizzes);
});

function viewQuiz(index) {
  alert(`Viewing quiz #${index + 1}`);
}

function editQuiz(index) {
  alert(`Editing quiz #${index + 1}`);
}

function deleteQuiz(index) {
  if (confirm("Are you sure you want to delete this quiz?")) {
    alert(`Quiz #${index + 1} deleted.`);
    // In real system: send DELETE request to backend
  }
}