# Quizify

Quizify is an easy-to-use online quiz and test platform designed for both educators/organizations and students. Whether in real or virtual classrooms, Quizify enhances learning experiences, promotes student engagement, and streamlines the evaluation process.

---

## 🚀 Features

- User Authentication (Login/Sign Up)
- Role-Based Access:
  - **Admin**: Manage users and platform settings
  - **Teacher**: Create and evaluate quizzes
  - **Student**: Attempt quizzes and view results
- Quiz Creation:
  - Support for both objective and subjective questions
- Quiz Participation and Evaluation
- Score Display and Result Tracking

---

## 🛠️ Technology Stack

### Frontend
- React.js
- Tailwind CSS (for responsive UI)
- React Router DOM

### Backend
- Node.js with Express.js
- Hosted on NGINX

### Database
- SQLite

### Deployment
- **Frontend**: Netlify
- **Backend**: NGINX server
- **Version Control**: Git + GitHub

---

## 📁 Project Structure

```bash
Quizify/
├── client/             # React frontend
├── server/             # Node + Express backend
├── database/           # SQLite database file and schema
├── .env.example        # Sample environment variables
├── README.md
└── ...

## 🧩 Getting Started

> **Note:** This section will be updated once deployment and environment setup are finalized.

To run the project locally:

1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/quizify.git

2. Navigate to the client and server directories and install dependencies:

cd client && npm install
cd ../server && npm install

Set up environment variables:

Create a .env file in the /server directory using .env.example as a reference.

Start the development servers:

npm run dev    # Concurrently runs frontend and backend

##👥 Contribution
This project is currently not open to public contributions. Only group members assigned to this student project may contribute.

##🔒 License
Private student project for educational use only.

##🌐 Live Demo
Coming soon – Deployment in progress.
