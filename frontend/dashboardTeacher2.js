// Default profile data (used for display only)
const defaultUserProfile = {
  name: "John Doe",
  profileImage: "https://placehold.co/128x128/cccccc/333333?text=Profile"
};

/**
 * Updates the profile pictures and user names on the dashboard.
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
}

/**
 * Handles the profile navigation.
 */
function handleProfile() {
  console.log("Navigating to teacher profile page...");
  window.location.href = "./TeacherProfilePage.html";
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
