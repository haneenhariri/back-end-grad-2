<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\UserAnswer;
use App\Services\StudentProgressService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentDashboardController extends Controller
{
    protected $progressService;

    public function __construct(StudentProgressService $progressService)
    {
        $this->progressService = $progressService;
    }

    public function getGeneralStats()
    {
        try {
            $user = Auth::user();

            // عدد الكورسات المسجل فيها
            $enrolledCourses = $user->courses()->count();

            // حساب متوسط التقدم في جميع الكورسات
            $courses = $user->courses()->get();
            $totalProgress = 0;
            $completedCourses = 0;
            $totalLessonsWatched = 0;

            foreach ($courses as $course) {
                $progress = $this->progressService->calculateCourseProgress($user->id, $course->id);
                $totalProgress += $progress['overall_progress'];

                if ($progress['overall_progress'] == 100) {
                    $completedCourses++;
                }

                $totalLessonsWatched += $progress['completed_lessons'];
            }

            $averageProgress = $courses->count() > 0 ? $totalProgress / $courses->count() : 0;

            return self::success([
                'enrolled_courses' => $enrolledCourses,
                'completed_courses' => $completedCourses,
                'total_lessons_watched' => $totalLessonsWatched,
                'average_progress' => round($averageProgress, 2)
            ]);
        } catch (\Exception $e) {
            Log::error('Student Dashboard Error: ' . $e->getMessage());
            return self::error('حدث خطأ أثناء جلب البيانات. يرجى المحاولة مرة أخرى.');
        }
    }

    public function getProgressStats()
    {
        try {
            $user = Auth::user();

            // الحصول على الكورسات المسجل فيها
            $courses = $user->courses()->get();

            $courseProgress = [];

            foreach ($courses as $course) {
                $progress = $this->progressService->calculateCourseProgress($user->id, $course->id);

                // الحصول على آخر نشاط
                $lastActivity = DB::table('student_progress')
                    ->where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->orderBy('updated_at', 'desc')
                    ->first();

                $courseProgress[] = [
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                    'progress_percentage' => $progress['overall_progress'],
                    'completed_lessons' => $progress['completed_lessons'],
                    'last_activity' => $lastActivity ? $lastActivity->updated_at : now()
                ];
            }

            return self::success($courseProgress);
        } catch (\Exception $e) {
            Log::error('Student Progress Stats Error: ' . $e->getMessage());
            return self::error('حدث خطأ أثناء جلب بيانات التقدم. يرجى المحاولة مرة أخرى.');
        }
    }

public function getExamResults()
{
    try {
        $user = Auth::user();
        \Log::info('Fetching exam results for user: ' . $user->id);

        // جلب كل الكورسات التي أجاب عليها الطالب
        $courses = \DB::table('user_answers')
            ->join('questions', 'user_answers.question_id', '=', 'questions.id')
            ->join('courses', 'questions.course_id', '=', 'courses.id')
            ->leftJoin('users as instructors', 'courses.instructor_id', '=', 'instructors.id')
            ->where('user_answers.user_id', $user->id)
            ->select(
                'courses.id as course_id',
                'courses.title as course_title',
                'courses.cover as course_image',
                'instructors.name as instructor_name'
            )
            ->distinct()
            ->get();

        if ($courses->isEmpty()) {
            return self::success([
                'status' => 'no_exams',
                'message' => 'You have not taken any exams yet'
            ]);
        }

        $results = [];

        foreach ($courses as $course) {
            $answers = \DB::table('user_answers')
                ->join('questions', 'user_answers.question_id', '=', 'questions.id')
                ->where('user_answers.user_id', $user->id)
                ->where('questions.course_id', $course->course_id)
                ->select('user_answers.mark')
                ->get();

            $hasPending = $answers->contains(function ($answer) {
                return is_null($answer->mark);
            });

            if ($hasPending) {
                $results[] = [
                    'status' => 'pending',
                    'message' => 'Not all questions have been graded by the instructor yet',
                    'course_id' => $course->course_id,
                    'course_title' => $course->course_title,
                    'course_image' => $course->course_image,
                    'instructor_name' => $course->instructor_name ?? 'Unknown Instructor',
                    'price' => $course->price ?? 0
                ];
            } else {
                $score = $answers->sum('mark');
                $total = $answers->count();

                $results[] = [
                    'status' => 'completed',
                    'course_id' => $course->course_id,
                    'course_title' => $course->course_title,
                    'course_image' => $course->course_image,
                    'instructor_name' => $course->instructor_name ?? 'Unknown Instructor',
                    'price' => $course->price ?? 0,
                    'score' => $score,
                    'total_questions' => $total,
                    'percentage' => $total > 0 ? round(($score / $total) * 100, 2) : 0
                ];
            }
        }

        return self::success($results);

    } catch (\Exception $e) {
        \Log::error("Exam results error: {$e->getMessage()}", [
            'trace' => $e->getTraceAsString()
        ]);

        return self::error('Failed to load exam results: ' . $e->getMessage());
    }
}

    public function getRecentActivity()
    {
        try {
            $user = Auth::user();

            // الحصول على آخر تحديثات التقدم
            $recentProgress = DB::table('student_progress')
                ->where('user_id', $user->id)
                ->orderBy('updated_at', 'desc')
                ->take(5)
                ->get();

            $progressActivity = [];

            foreach ($recentProgress as $progress) {
                $course = Course::find($progress->course_id);
                if ($course) {
                    $progressActivity[] = [
                        'type' => 'progress',
                        'course_id' => $progress->course_id,
                        'course_title' => $course->title,
                        'details' => "تقدم في الكورس بنسبة " . ($progress->progress_percentage ?? 0) . "%",
                        'date' => $progress->updated_at
                    ];
                }
            }

            // الحصول على آخر إجابات الاختبارات
            $recentExams = UserAnswer::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $examActivity = [];

            foreach ($recentExams as $answer) {
                $course = Course::find($answer->course_id);
                if ($course) {
                    $examActivity[] = [
                        'type' => 'exam',
                        'cover' => $course->cover,
                        'course_id' => $answer->course_id,
                        'course_title' => $course->title,
                        'details' => "إجابة على سؤال بعلامة {$answer->mark}",
                        'date' => $answer->created_at
                    ];
                }
            }

            // دمج النشاطات وترتيبها حسب التاريخ
            $allActivity = array_merge($progressActivity, $examActivity);
            usort($allActivity, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            return self::success(array_slice($allActivity, 0, 10));
        } catch (\Exception $e) {
            Log::error('Recent Activity Error: ' . $e->getMessage());
            return self::error('حدث خطأ أثناء جلب بيانات النشاط الأخير. يرجى المحاولة مرة أخرى.');
        }
    }

    public function getRecommendedCourses()
    {
        try {
            $user = Auth::user();

            // الحصول على الفئات التي يهتم بها الطالب
            $userCategories = $user->courses()
                ->pluck('category_id')
                ->unique()
                ->filter();

            // الحصول على الكورسات الموصى بها من نفس الفئات
            $recommendedCourses = [];

            if ($userCategories->count() > 0) {
                $recommendedCourses = Course::whereIn('category_id', $userCategories)
                    ->where('status', 'accepted')
                    ->whereDoesntHave('users', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->with(['instructor:id,user_id', 'instructor.user:id,name', 'category:id,name'])
                    ->take(5)
                    ->get()
                    ->map(function ($course) {
                        return [
                            'id' => $course->id,
                            'title' => $course->title,
                            'price' => $course->price,
                            'instructor' => [
                                'id' => $course->instructor->user->id,
                                'name' => $course->instructor->user->name
                            ],
                            'category' => [
                                'id' => $course->category->id,
                                'name' => $course->category->name
                            ]
                        ];
                    });
            }

            return self::success($recommendedCourses);
        } catch (\Exception $e) {
            Log::error('Recommended Courses Error: ' . $e->getMessage());
            return self::error('حدث خطأ أثناء جلب الكورسات الموصى بها. يرجى المحاولة مرة أخرى.');
        }
    }
}







