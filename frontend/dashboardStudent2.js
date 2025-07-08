document.addEventListener('DOMContentLoaded', () => {
    // Sample quiz data for students
    // This data can be expanded or fetched from a backend
    const studentQuizData = {
        '5A': {
            'Mathematics': [
                { subject: 'Mathematics', quizName: 'Algebra Basics Quiz', score: '85/100', grade: 'A-' },
                { subject: 'Mathematics', quizName: 'Geometry Fundamentals', score: '75/100', grade: 'B' }
            ],
            'Science': [
                { subject: 'Science', quizName: 'Ecology Fundamentals', score: '90/100', grade: 'A' },
                { subject: 'Sains', quizName: 'Ujian Mikro: Elektrik', score: '85/100', grade: 'B+' }
            ],
            'History': [
                { subject: 'History', quizName: 'Ancient Civilizations', score: '78/100', grade: 'B+' }
            ],
            'Bahasa Melayu': [
                { subject: 'Bahasa Melayu', quizName: 'Tatabahasa Ayat Majmuk', score: '78/100', grade: 'B' }
            ],
            'Sejarah': [
                { subject: 'Sejarah', quizName: 'Kuiz Online : Sejarah Dunia', score: '90/100', grade: 'A+' }
            ],
            'Sains': [
                { subject: 'Sains', quizName: 'Kuiz Pintar: Rangkaian Makanan', score: '100/100', grade: 'A+' }
            ]
        },
        '5B': {
            'Mathematics': [
                { subject: 'Mathematics', quizName: 'Calculus Introduction', score: '65/100', grade: 'D' }
            ],
            'Science': [
                { subject: 'Science', quizName: 'Chemistry Basics', score: '80/100', grade: 'B+' }
            ],
            'English': [
                { subject: 'English', quizName: 'Grammar Test', score: '92/100', grade: 'A' }
            ]
        },
        '6A': {
            'Physics': [
                { subject: 'Physics', quizName: 'Quantum Mechanics', score: '95/100', grade: 'A' }
            ],
            'Chemistry': [
                { subject: 'Chemistry', quizName: 'Organic Chemistry', score: '88/100', grade: 'A-' }
            ]
        }
    };

    const classSelect = document.getElementById('classSelect');
    const subjectSelect = document.getElementById('subjectSelect');
    const quizTableBody = document.getElementById('quizTableBody');

    const sidebarUserName = document.getElementById('sidebar-user-name');
    const sidebarProfilePicture = document.getElementById('sidebar-profile-picture');
    const bannerUserName = document.getElementById('banner-user-name');
    const bannerProfilePicture = document.getElementById('banner-profile-picture');

    // Function to render quiz table based on filtered data
    function renderQuizTable(data) {
        quizTableBody.innerHTML = ''; // Clear existing rows
        if (data.length === 0) {
            const noDataRow = document.createElement('tr');
            noDataRow.innerHTML = `<td colspan="5" class="py-3 px-4 text-center text-gray-500">No quiz data available for the selected class and subject.</td>`;
            quizTableBody.appendChild(noDataRow);
            return;
        }

        data.forEach(quiz => {
            const row = document.createElement('tr');
            row.classList.add('hover:bg-gray-50', 'transition', 'duration-150');

            // Determine grade color
            let gradeColorClass = 'text-gray-700';
            if (quiz.grade.includes('A')) {
                gradeColorClass = 'text-green-600';
            } else if (quiz.grade.includes('B')) {
                gradeColorClass = 'text-blue-600';
            } else if (quiz.grade.includes('C')) {
                gradeColorClass = 'text-yellow-600';
            } else if (quiz.grade.includes('D')) {
                gradeColorClass = 'text-orange-600';
            } else if (quiz.grade.includes('F')) {
                gradeColorClass = 'text-red-600';
            }

            row.innerHTML = `
                <td class="py-3 px-4 whitespace-nowrap text-sm font-medium text-gray-900">${quiz.subject}</td>
                <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-700">${quiz.quizName}</td>
                <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-700">${quiz.score}</td>
                <td class="py-3 px-4 whitespace-nowrap text-sm ${gradeColorClass} font-semibold">${quiz.grade}</td>
                <td class="py-3 px-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                    <a href="#" class="text-red-600 hover:text-red-900">Delete</a>
                </td>
            `;
            quizTableBody.appendChild(row);
        });
    }

    // Function to filter and update the table
    function filterAndRenderTable() {
        const selectedClass = classSelect.value;
        const selectedSubject = subjectSelect.value;

        let filteredQuizzes = [];

        if (selectedClass && studentQuizData[selectedClass]) {
            if (selectedSubject && studentQuizData[selectedClass][selectedSubject]) {
                // Filter by both class and subject
                filteredQuizzes = studentQuizData[selectedClass][selectedSubject];
            } else if (!selectedSubject) {
                // If only class is selected, show all quizzes for that class
                for (const subject in studentQuizData[selectedClass]) {
                    filteredQuizzes = filteredQuizzes.concat(studentQuizData[selectedClass][subject]);
                }
            }
        }
        renderQuizTable(filteredQuizzes);
    }

    // Event listeners for class and subject selection changes
    classSelect.addEventListener('change', filterAndRenderTable);
    subjectSelect.addEventListener('change', filterAndRenderTable);

    // Initial load: display a message or default data
    // For now, let's display a message to select a class/subject
    quizTableBody.innerHTML = `<td colspan="5" class="py-3 px-4 text-center text-gray-500">Please select a class and/or subject to view quiz data.</td>`;

    // Example: Set user name and profile picture (you'd get this from your backend)
    const userName = "John Doe"; // Replace with actual user name
    const userClass = "5A"; // Replace with actual user class
    const profilePicUrl = "https://placehold.co/128x128/a7c5ed/ffffff?text=Profile"; // Replace with actual profile picture URL

    if (sidebarUserName) sidebarUserName.textContent = userName;
    if (bannerUserName) bannerUserName.textContent = userName;
    if (sidebarProfilePicture) sidebarProfilePicture.src = profilePicUrl;
    if (bannerProfilePicture) bannerProfilePicture.src = profilePicUrl;

    // Update class display in sidebar and banner
    const sidebarClassDisplay = document.querySelector('aside .text-base');
    const bannerClassDisplay = document.querySelector('main .text-blue-200');

    if (sidebarClassDisplay) sidebarClassDisplay.textContent = `Class: ${userClass}`;
    if (bannerClassDisplay) bannerClassDisplay.textContent = `Class: ${userClass}`;
});
