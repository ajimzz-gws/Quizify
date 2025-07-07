// Firebase imports
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
import { getAuth, signInAnonymously, signInWithCustomToken } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
import { getFirestore, doc, getDoc, setDoc } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

// Firebase variables
let app, db, auth, userId, appId;

// Default profile data
let userProfile = {
  name: "John Doe",
  email: "john.doe@example.com",
  phone: "+123 456 7890",
  dob: "1980-01-01",
  id: "TCH001",
  department: "Science",
  classesTaught: "5A, 5B, 6C",
  profileImage: "https://placehold.co/128x128/cccccc/333333?text=Profile"
};

// Load profile data into form
function loadProfileData() {
  document.getElementById('name').value = userProfile.name;
  document.getElementById('email').value = userProfile.email;
  document.getElementById('phone').value = userProfile.phone;
  document.getElementById('dob').value = userProfile.dob;
  document.getElementById('id_number').value = userProfile.id;
  document.getElementById('department').value = userProfile.department;
  document.getElementById('classes_taught').value = userProfile.classesTaught;
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
  userProfile.department = document.getElementById('department').value;
  userProfile.classesTaught = document.getElementById('classes_taught').value;
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
  window.location.href = "./dashboardStudent.html";
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
