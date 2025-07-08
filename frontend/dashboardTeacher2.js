document.addEventListener('DOMContentLoaded', () => {
    // Sample quiz data, organized by class
    const quizData = {
        '5A': [
            { subject: 'Mathematics', quizName: 'Algebra Basics Quiz', score: '85/100', grade: 'A-' },
            { subject: 'Science', quizName: 'Ecology Fundamentals', score: '90/100', grade: 'A' },
            { subject: 'History', quizName: 'Ancient Civilizations', score: '78/100', grade: 'B+' }
        ],
        '5B': [
            { subject: 'Mathematics', quizName: 'Geometry Introduction', score: '70/100', grade: 'C' },
            { subject: 'Science', quizName: 'Human Body Systems', score: '88/100', grade: 'A-' },
            { subject: 'English', quizName: 'Literary Devices', score: '92/100', grade: 'A' }
        ],
        '6A': [
            { subject: 'Physics', quizName: 'Newtonian Mechanics', score: '95/100', grade: 'A' },
            { subject: 'Chemistry', quizName: 'Periodic Table', score: '82/100', grade: 'B' },
            { subject: 'Computer Science', quizName: 'Basic Programming', score: '75/100', grade: 'B-' }
        ]
    };

    const classSelect = document.getElementById('classSelect');
    const quizTableBody = document.getElementById('quizTableBody');
    const sidebarUserName = document.getElementById('sidebar-user-name');
    const sidebarProfilePicture = document.getElementById('sidebar-profile-picture');
    const bannerUserName = document.getElementById('banner-user-name');
    const bannerProfilePicture = document.getElementById('banner-profile-picture');

    // Function to render quiz table
    function renderQuizTable(data) {
        quizTableBody.innerHTML = ''; // Clear existing rows
        if (data.length === 0) {
            const noDataRow = document.createElement('tr');
            noDataRow.innerHTML = `<td colspan="5" class="py-3 px-4 text-center text-gray-500">No quiz data available for this class.</td>`;
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

    // Event listener for class selection change
    classSelect.addEventListener('change', (event) => {
        const selectedClass = event.target.value;
        if (selectedClass) {
            const quizzesForClass = quizData[selectedClass] || [];
            renderQuizTable(quizzesForClass);
        } else {
            // If "Select Class" is chosen (empty value), clear the table
            quizTableBody.innerHTML = `<td colspan="5" class="py-3 px-4 text-center text-gray-500">Please select a class to view quiz data.</td>`;
        }
    });

    // Initial load: display a message or default class data
    // For now, let's display a message to select a class
    quizTableBody.innerHTML = `<td colspan="5" class="py-3 px-4 text-center text-gray-500">Please select a class to view quiz data.</td>`;

    // Example: Set user name and profile picture (you'd get this from your backend)
    const userName = "John Doe"; // Replace with actual user name
    const profilePicUrl = "https://placehold.co/128x128/a7c5ed/ffffff?text=Profile"; // Replace with actual profile picture URL

    if (sidebarUserName) sidebarUserName.textContent = userName;
    if (bannerUserName) bannerUserName.textContent = userName;
    if (sidebarProfilePicture) sidebarProfilePicture.src = profilePicUrl;
    if (bannerProfilePicture) bannerProfilePicture.src = profilePicUrl;
});
