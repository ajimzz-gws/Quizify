// Default profile data (used for display only)
const defaultUserProfile = {
  name: "John Doe",
  email: "johndoe@example.com", // Added default email
  profileImage: "https://placehold.co/128x128/cccccc/333333?text=Profile"
};

// Sample quiz data (replace with actual data from a database if available)
// In a real application, this data would likely be fetched from Firestore
// based on the selected class.
const quizzesData = [
  { class: '5A', subject: 'Mathematics', quizName: 'Algebra Quiz', score: '85/100', grade: 'B+' },
  { class: '5A', subject: 'Science', quizName: 'Biology Basics', score: '92/100', grade: 'A' },
  { class: '5B', subject: 'History', quizName: 'World War II', score: '78/100', grade: 'C+' },
  { class: '5A', subject: 'English', quizName: 'Grammar Test', score: '70/100', grade: 'C-' },
  { class: '6A', subject: 'Physics', quizName: 'Kinematics', score: '95/100', grade: 'A+' },
  { class: '5B', subject: 'Mathematics', quizName: 'Geometry Fundamentals', score: '88/100', grade: 'B' },
  { class: '6A', subject: 'Chemistry', quizName: 'Periodic Table', score: '80/100', grade: 'B-' },
];

// Get references to the HTML elements for the quiz table and class selection
const classSelect = document.getElementById('classSelect');
const quizTableBody = document.getElementById('quizTableBody');

/**
 * Updates the profile pictures, user names, and emails on the dashboard.
 * @param {string} imageUrl - The URL of the profile image.
 * @param {string} userName - The name of the user.
 * @param {string} userEmail - The email of the user.
 */
function updateDashboard(imageUrl, userName) {
  const sidebarProfilePicture = document.getElementById("sidebar-profile-picture");
  const bannerProfilePicture = document.getElementById("banner-profile-picture");
  const sidebarUserName = document.getElementById("sidebar-user-name");
  const bannerUserName = document.getElementById("banner-user-name");

  if (sidebarProfilePicture && imageUrl) sidebarProfilePicture.src = imageUrl;
  if (bannerProfilePicture && imageUrl) bannerProfilePicture.src = imageUrl;
  if (sidebarUserName && userName) sidebarUserName.textContent = userName;
  if (bannerUserName && userName) bannerUserName.textContent = userName;

  // Update email display. Ensure these elements exist in your HTML with these IDs.
  if (sidebarUserEmail && userEmail) sidebarUserEmail.textContent = `Email: ${userEmail}`;
  if (bannerUserEmail && userEmail) bannerUserEmail.textContent = `Email: ${userEmail}`;
}

/**
 * Handles the profile navigation.
 */
function handleProfile() {
  console.log("Navigating to teacher profile page...");
  window.location.href = "./TeacherProfilePage.html"; // Assuming this page exists
}

/**
 * Handles the logout action (no Firebase, just redirect).
 */
function handleLogout() {
  console.log("Simulated logout.");
  window.location.href = "./login.html";
}

// ✅ Run after DOM is fully loaded
document.addEventListener("DOMContentLoaded", function () {
  // Update dashboard with default data
  updateDashboard(defaultUserProfile.profileImage, defaultUserProfile.name);

  // Attach button listeners
  const profileBtn = document.getElementById("profile-btn");
  const logoutBtn = document.getElementById("logout-btn");

  if (profileBtn) {
    profileBtn.addEventListener("click", function (e) {
      e.preventDefault();
      handleProfile();
    });
  }

  if (logoutBtn) {
    logoutBtn.addEventListener("click", function (e) {
      e.preventDefault();
      handleLogout();
    });
  }
});
