// Firebase imports
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
import { getAuth, signInAnonymously, signInWithCustomToken } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
import { getFirestore, doc, getDoc } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

// Firebase variables
let app, db, auth, userId, appId;

// Default profile data
let defaultUserProfile = {
  name: "John Doe",
  profileImage: "https://placehold.co/128x128/cccccc/333333?text=Profile"
};

/**
 * Updates the profile pictures and user names on the dashboard.
 */
function updateDashboard(imageUrl, userName) {
  const sidebarProfilePicture = document.getElementById('sidebar-profile-picture');
  const bannerProfilePicture = document.getElementById('banner-profile-picture');
  const sidebarUserName = document.getElementById('sidebar-user-name');
  const bannerUserName = document.getElementById('banner-user-name');

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

// ✅ Initialize Firebase and load profile data
window.onload = async function () {
  appId = typeof __app_id !== 'undefined' ? __app_id : 'default-app-id';
  const firebaseConfig = JSON.parse(typeof __firebase_config !== 'undefined' ? __firebase_config : '{}');
  const initialAuthToken = typeof __initial_auth_token !== 'undefined' ? __initial_auth_token : null;

  if (Object.keys(firebaseConfig).length === 0) {
    console.error("Dashboard: Firebase config is missing or empty.");
    return;
  }

  try {
    app = initializeApp(firebaseConfig);
    db = getFirestore(app);
    auth = getAuth(app);

    if (initialAuthToken) {
      await signInWithCustomToken(auth, initialAuthToken);
      console.log("Dashboard: Signed in with custom token.");
    } else {
      await signInAnonymously(auth);
      console.log("Dashboard: Signed in anonymously.");
    }

    userId = auth.currentUser?.uid || crypto.randomUUID();
    console.log("Dashboard: User ID:", userId);

    const profileRef = doc(db, `artifacts/${appId}/users/${userId}/userProfiles`, "myProfile");
    const profileSnap = await getDoc(profileRef);

    let currentProfileData = defaultUserProfile;

    if (profileSnap.exists()) {
      currentProfileData = profileSnap.data();
      console.log("Dashboard: Loaded profile from Firestore:", currentProfileData);
    } else {
      console.log("Dashboard: No existing profile found. Using default.");
    }

    updateDashboard(currentProfileData.profileImage, currentProfileData.name);

    // ✅ Attach logout button after auth is ready
    const logoutBtn = document.getElementById("logout-btn");
    if (logoutBtn) {
      logoutBtn.addEventListener("click", function (e) {
        e.preventDefault();
        auth.signOut().then(() => {
          console.log("Teacher logged out.");
          window.location.href = "./login.html";
        }).catch((error) => {
          console.error("Error signing out:", error);
        });
      });
    }

  } catch (error) {
    console.error("Dashboard: Firebase initialization or data loading error:", error);
    updateDashboard(defaultUserProfile.profileImage, defaultUserProfile.name);
  }
};

// ✅ Profile button can stay in DOMContentLoaded (doesn't depend on Firebase)
document.addEventListener("DOMContentLoaded", function () {
  const profileBtn = document.getElementById("profile-btn");

  if (profileBtn) {
    profileBtn.addEventListener("click", function (e) {
      e.preventDefault();
      handleProfile();
    });
  }

    // ✅ Handle Logout button
      if (logoutBtn) {
        logoutBtn.addEventListener("click", function (e) {
          e.preventDefault();
          auth.signOut().then(() => {
            console.log("Student logged out.");
            window.location.href = "./login.html";
          }).catch((error) => {
            console.error("Error signing out:", error);
          });
        });
      }
});
