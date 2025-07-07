<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Quiz;
use App\Models\QuizAttempt;

class DashboardController extends Controller
{
    public function getDashboard()
    {
        $user = Auth::user();

        if ($user->role === 'teacher') {
            $quizzes = Quiz::where('created_by', $user->id)->withCount('attempts')->get();

            return response()->json([
                'role' => 'teacher',
                'quiz_count' => $quizzes->count(),
                'quizzes' => $quizzes,
            ]);
        }

        if ($user->role === 'student') {
            $attempts = QuizAttempt::where('user_id', $user->id)->with('quiz')->get();
            $averageScore = $attempts->avg('score');

            return response()->json([
                'role' => 'student',
                'attempt_count' => $attempts->count(),
                'average_score' => round($averageScore),
                'attempts' => $attempts,
            ]);
        }

        return response()->json(['error' => 'Unknown role'], 403);
    }
}