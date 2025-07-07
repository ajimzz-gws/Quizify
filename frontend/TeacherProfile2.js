// Firebase imports
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
import { getAuth, signInAnonymously, signInWithCustomToken } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
import { getFirestore, doc, getDoc, setDoc } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

// Firebase variables
let app, db, auth, userId, appId;

// Default profile data for a teacher
let userProfile = {
  name: "Jane Smith",
  email: "jane.smith@example.com",
  phone: "+987 654 3210",
  dob: "1975-05-15",
  teacherId: "TCH002",
  subject: "Mathematics",
  gradeLevels: "7, 8, 9",
  yearsExperience: 10,
  profileImage: "https://placehold.co/128x128/cccccc/333333?text=Teacher"
};

// Load profile data into form
function loadProfileData() {
  document.getElementById('name').value = userProfile.name;
  document.getElementById('email').value = userProfile.email;
  document.getElementById('phone').value = userProfile.phone;
  document.getElementById('dob').value = userProfile.dob;
  document.getElementById('teacher_id').value = userProfile.teacherId;
  document.getElementById('subject').value = userProfile.subject;
  document.getElementById('grade_levels').value = userProfile.gradeLevels;
  document.getElementById('years_experience').value = userProfile.yearsExperience;
  document.getElementById('profile-picture').src = userProfile.profileImage;
  document.getElementById('display-name').textContent = userProfile.name;
  console.log("Profile Page: Data loaded into form:", userProfile);
}

// Save profile changes to Firestore
async function saveProfileChanges() {
  userProfile.name = document.getElementById('name').value;
  userProfile.email = document.getElementById('email').value;
  userProfile.phone = document.getElementById('phone').value;
  userProfile.dob = document.getElementById('dob').value;
  userProfile.subject = document.getElementById('subject').value;
  userProfile.gradeLevels = document.getElementById('grade_levels').value;
  userProfile.yearsExperience = document.getElementById('years_experience').value;
  userProfile.profileImage = document.getElementById('profile-picture').src;

  document.getElementById('display-name').textContent = userProfile.name;

  console.log("Profile Page: Attempting to save profile changes to Firestore:", userProfile);

  try {
    if (!db || !userId || !appId) {
      console.error("Firestore not initialized or userId/appId missing.");
      return;
    }

    const profileRef = doc(db, `artifacts/${appId}/users/${userId}/userProfiles`, "myProfile");
    await setDoc(profileRef, userProfile);
    console.log("Profile Page: Profile changes saved to Firestore successfully!");
    goBackToDashboard();
  } catch (error) {
    console.error("Profile Page: Error saving profile to Firestore:", error);
  }
}

// Navigate back to dashboard
function goBackToDashboard() {
  console.log("Profile Page: Navigating back to dashboard.");
  window.location.href = "./dashboardTeachers.html";
}

// Preview selected image
function previewImage(event) {
  const profilePicture = document.getElementById('profile-picture');
  const file = event.target.files[0];

  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      profilePicture.src = e.target.result;
      userProfile.profileImage = e.target.result;
      console.log("Profile Page: Image preview updated. New image data length:", e.target.result.length);
    };
    reader.readAsDataURL(file);
  }
}

// Initialize Firebase and load profile
window.onload = async function () {
  appId = typeof __app_id !== 'undefined' ? __app_id : 'default-app-id';
  const firebaseConfig = JSON.parse(typeof __firebase_config !== 'undefined' ? __firebase_config : '{}');
  const initialAuthToken = typeof __initial_auth_token !== 'undefined' ? __initial_auth_token : null;

  if (Object.keys(firebaseConfig).length === 0) {
    console.error("Firebase config is missing or empty.");
    return;
  }

  try {
    app = initializeApp(firebaseConfig);
    db = getFirestore(app);
    auth = getAuth(app);

    if (initialAuthToken) {
      await signInWithCustomToken(auth, initialAuthToken);
      console.log("Profile Page: Signed in with custom token.");
    } else {
      await signInAnonymously(auth);
      console.log("Profile Page: Signed in anonymously.");
    }

    userId = auth.currentUser?.uid || crypto.randomUUID();
    console.log("Profile Page: User ID:", userId);

    const profileRef = doc(db, `artifacts/${appId}/users/${userId}/userProfiles`, "myProfile");
    const profileSnap = await getDoc(profileRef);

    if (profileSnap.exists()) {
      userProfile = profileSnap.data();
      console.log("Profile Page: Loaded profile from Firestore:", userProfile);
    } else {
      console.log("Profile Page: No existing profile found in Firestore. Using default.");
    }

    loadProfileData();
  } catch (error) {
    console.error("Profile Page: Firebase initialization or data loading error:", error);
  }
};

// Attach button events after DOM is ready
document.addEventListener("DOMContentLoaded", function () {
  const saveBtn = document.getElementById("save-btn");
  const backBtn = document.getElementById("back-btn");
  const imageInput = document.getElementById("image-upload");
  const profileBtn = document.getElementById("profile-btn");
  const logoutBtn = document.getElementById("logout-btn");

  if (saveBtn) {
    saveBtn.addEventListener("click", saveProfileChanges);
  }

  if (backBtn) {
    backBtn.addEventListener("click", goBackToDashboard);
  }

  if (imageInput) {
    imageInput.addEventListener("change", previewImage);
  }

  if (profileBtn) {
    profileBtn.addEventListener("click", function () {
      window.location.href = "profile.html"; // or current page
    });
  }

  if (logoutBtn) {
    logoutBtn.addEventListener("click", function () {
      const auth = getAuth();
      auth.signOut().then(() => {
        console.log("User signed out.");
        window.location.href = "login.html"; // redirect to login page
      }).catch((error) => {
        console.error("Error signing out:", error);
      });
    });
  }
});
