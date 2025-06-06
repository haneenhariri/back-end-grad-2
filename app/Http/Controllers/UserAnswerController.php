<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAnswer\StoreRequest;
use App\Http\Requests\UserAnswer\UpdateRequest;
use App\Http\Resources\QuestionResource;
use App\Http\Resources\UserAnswerResource;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\User;
use App\Models\UserAnswer;
use App\Services\UserAnswerService;
use Illuminate\Support\Facades\Auth;


class UserAnswerController extends Controller
{
    protected $userAnswerService;

    public function __construct(UserAnswerService $userAnswerService)
    {
        $this->userAnswerService = $userAnswerService;
        $this->middleware('role:student')->only('store');
        $this->middleware('role:instructor')->only(['index', 'update']);

    }
    /**
     * Get all courses owned by the authenticated instructor
     */
public function getInstructorCourses()
{
    $instructorId = auth()->id();

    $courses = Course::withCount([
        'answers as students_answered_count' => function ($query) {
            $query->select(\DB::raw('COUNT(DISTINCT user_id)'));
        }
    ])
    ->where('instructor_id', $instructorId)
    ->get()
    ->map(function ($course) {
        return [
            'id' => $course->id,
            'title' => $course->title,
            'students_answered_count' => $course->students_answered_count,
        ];
    });

    return response()->json([
        'status' => 'success',
        'message' => 'Courses with student answers retrieved successfully.',
        'data' => $courses,
    ]);
}


/**
 * Get all student answers for a specific course
 */
public function getAllStudentAnswersForCourse($courseId)
{
    // الحصول على جميع إجابات الطلاب للكورس المحدد
    $answers = UserAnswer::with(['user', 'question'])
        ->whereHas('question', function($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })
        ->orderBy('user_id')
        ->orderBy('question_id')
        ->get()
        ->groupBy('user_id');

    // تنسيق البيانات للإرجاع
    $formattedAnswers = $answers->map(function ($userAnswers, $userId) {
        $user = $userAnswers->first()->user;
        
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'answers' => $userAnswers->map(function ($answer) {
                return [
                    'question_id' => $answer->question_id,
                    'question_text' => $answer->question->question,
                    'answer' => $answer->answer,
                    'mark' => $answer->mark,
                    'max_mark' => $answer->question->mark,
                    'answered_at' => $answer->created_at,
                ];
            }),
            'total_mark' => $userAnswers->sum('mark'),
            'total_max_mark' => $userAnswers->sum(function ($answer) {
                return $answer->question->mark;
            }),
        ];
    });

    return self::success($formattedAnswers->values());
}


    /**
     * Display a listing of the resource.
     */
    public function index(User $user, $course)
    {
        $answers = $user->questions()->where('course_id', $course)->where('type', 'code')
            ->withPivot(['answer', 'mark'])
            ->get();
        return self::success(QuestionResource::collection($answers));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validationData();
        $this->userAnswerService->store($data);
        return self::success(null, 'added successfully');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, UserAnswer $userAnswer)
    {
        $userAnswer = $this->userAnswerService->update($request->mark, $userAnswer);
        return self::success(new UserAnswerResource($userAnswer));
    }

    public function testResult($courseId)
    {
        $result = $this->userAnswerService->testResult($courseId);
        return self::success($result);
    }


}

