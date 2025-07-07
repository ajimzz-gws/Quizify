<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Feedback;


class QuizController extends Controller
{
    /**
     * Create and store a new quiz.
     */
    public function createQuiz(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'time_limit' => 'nullable|integer',
            'is_randomized' => 'boolean',
            'questions' => 'required|array'
        ]);

        $quiz = Quiz::create([
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => auth()->id(),
            'category' => $request->category,
            'time_limit' => $request->time_limit ?? 300,
            'is_randomized' => $request->is_randomized ?? false,
            'questions_json' => json_encode($request->questions),
        ]);

        return response()->json(['message' => 'Quiz created', 'quiz' => $quiz], 201);
    }

    /**
     * List all available quizzes.
     */
    public function listQuizzes()
    {
        $quizzes = Quiz::orderBy('created_at', 'desc')
            ->select('id', 'title', 'category', 'description', 'time_limit')
            ->get();

        return response()->json($quizzes);
    }
    //list quizzes for teachers
    public function listTeacherQuizzes()
    {
        $quizzes = Quiz::where('created_by', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($quizzes);
    }


    /**
     * Show quiz details including decoded questions.
     */
    public function showQuiz($id)
    {
        $quiz = Quiz::findOrFail($id);

        return response()->json([
            'id' => $quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'category' => $quiz->category,
            'time_limit' => $quiz->time_limit,
            'is_randomized' => $quiz->is_randomized,
            'questions' => json_decode($quiz->questions_json),
        ]);
    }

    /**
     * Submit a user's attempt and store result.
     */
    public function storeResult(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'score' => 'required|integer',
            'answers' => 'required|array'
        ]);

        $attempt = QuizAttempt::create([
            'user_id' => auth()->id(), // or pass user_id if not using session
            'quiz_id' => $request->quiz_id,
            'score' => $request->score,
            'answers_json' => json_encode($request->answers),
            'submitted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Quiz attempt saved successfully',
            'attempt_id' => $attempt->id
        ]);
    }


    /**
     * Optional: Fetch trivia from OpenTDB API.
     */
    public function fetchTrivia()
    {
        $response = Http::get('https://opentdb.com/api.php?amount=10&type=multiple');
        return $response->json();
    }

    /**
     * Optional: View all quiz attempts for authenticated user.
     */
    public function viewAttempts()
    {
        $attempts = QuizAttempt::where('user_id', auth()->id())->orderBy('submitted_at', 'desc')->get();
        return response()->json($attempts);
    }

    //Submit feedback for a quiz
    public function submitFeedback(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $feedback = Feedback::create([
            'user_id' => auth()->id(),
            'quiz_id' => $request->quiz_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['message' => 'Feedback submitted', 'feedback_id' => $feedback->id]);
    }

    //view feedback for a quiz
    public function viewFeedback($quizId)
    {
        $feedbackList = Feedback::where('quiz_id', $quizId)
            ->with('user:id,id,full_name,email') // Optional: include user details
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($feedbackList);
    }


}