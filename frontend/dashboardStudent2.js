import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
import { getAuth } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";

// ✅ Your Firebase config (replace with your actual values)
const firebaseConfig = {
  apiKey: "YOUR_API_KEY",
  authDomain: "YOUR_AUTH_DOMAIN",
  projectId: "YOUR_PROJECT_ID",
  storageBucket: "YOUR_STORAGE_BUCKET",
  messagingSenderId: "YOUR_MESSAGING_SENDER_ID",
  appId: "YOUR_APP_ID"
};

// ✅ Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

document.addEventListener("DOMContentLoaded", function () {
  const profileBtn = document.getElementById("profile-btn");
  const logoutBtn = document.getElementById("logout-btn");

  // ✅ Handle Profile button
  if (profileBtn) {
    profileBtn.addEventListener("click", function (e) {
      e.preventDefault();
      window.location.href = "StudentProfilePage.html";
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
