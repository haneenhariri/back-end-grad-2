<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Rate;

class InstructorDashboardService
{
    public function getStats()
    {
        try {
            $instructorId = auth()->id();
            $accountId = auth()->user()->account_id;

            $coursesCount = Course::where('instructor_id', $instructorId)->count();

            $studentsCount = DB::table('course_user')
                ->join('courses', 'course_user.course_id', '=', 'courses.id')
                ->where('courses.instructor_id', $instructorId)
                ->count(DB::raw('distinct course_user.user_id'));

            $totalRevenue = Transaction::where('account_id', $accountId)
                ->whereIn('course_id', function ($query) use ($instructorId) {
                    $query->select('id')
                          ->from('courses')
                          ->where('instructor_id', $instructorId);
                })
                ->sum('amount');

            $averageRating = Rate::avg('rate') ?: 0;

            // استعلام لحساب عدد الطلاب لكل كورس وترتيبهم
            $topCourses = Course::select('courses.*', DB::raw('(SELECT COUNT(*) FROM course_user WHERE course_user.course_id = courses.id) as students_count'))
                ->where('instructor_id', $instructorId)
                ->orderByDesc('students_count')
                ->limit(5)
                ->get();

            return [
                'courses_count' => $coursesCount,
                'students_count' => $studentsCount,
                'total_revenue' => $totalRevenue,
                'average_rating' => round($averageRating, 2),
                'top_courses' => $topCourses,
            ];
        } catch (\Exception $e) {
            Log::error('Error in InstructorDashboardService@getStats: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'status' => 'error',
                'message' => 'A database query error occurred.',
                'errors' => $e->getMessage(),
            ];
        }
    }
}
