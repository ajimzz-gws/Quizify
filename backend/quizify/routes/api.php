<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\DashboardController;

// Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Quiz routes — no Sanctum middleware needed
Route::post('/quizzes/create', [QuizController::class, 'createQuiz']);
Route::get('/quizzes', [QuizController::class, 'listQuizzes']);
Route::get('/quizzes/{id}', [QuizController::class, 'showQuiz']);
Route::post('/quizzes/submit', [QuizController::class, 'storeResult']);
Route::get('/quiz-attempts', [QuizController::class, 'viewAttempts']);
Route::get('/trivia', [QuizController::class, 'fetchTrivia']);

// Dashboard route
Route::get('/dashboard', [DashboardController::class, 'getDashboard']);

// Feedback routes
Route::post('/quizzes/feedback', [QuizController::class, 'submitFeedback']);
Route::get('/quizzes/{id}/feedback', [QuizController::class, 'viewFeedback']);