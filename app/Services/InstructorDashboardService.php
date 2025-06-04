<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Rate;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Account;

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
    public function getRevenueStatsInst($period = 'monthly')
    {
        $now = Carbon::now();

        switch ($period) {
            case 'daily':
                $startDate = $now->copy()->subDays(30);
                $groupBy = 'date';
                $dateFormat = '%Y-%m-%d';
                break;
            case 'weekly':
                $startDate = $now->copy()->subWeeks(12);
                $groupBy = 'week';
                $dateFormat = '%Y-%u';  // ISO week number
                break;
            case 'yearly':
                $startDate = $now->copy()->subYears(5);
                $groupBy = 'year';
                $dateFormat = '%Y';
                break;
            case 'this_week':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                $groupBy = 'date';
                $dateFormat = '%Y-%m-%d';
                break;
            case 'this_month':
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $groupBy = 'date';
                $dateFormat = '%Y-%m-%d';
                break;
            case 'this_year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                $groupBy = 'month';
                $dateFormat = '%Y-%m';
                break;
            case 'monthly':
            default:
                $startDate = $now->copy()->subMonths(12);
                $groupBy = 'month';
                $dateFormat = '%Y-%m';
                break;
        }

        // إجمالي الإيرادات حسب الفترة
        $totalRevenueQuery = Transaction::select(
            DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
            DB::raw('SUM(amount) as total')
        )
        ->where('created_at', '>=', $startDate);

        // إيرادات المنصة حسب الفترة (المعاملات التي تذهب للحساب رقم 1)
        $platformRevenueQuery = Transaction::select(
            DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
            DB::raw('SUM(amount) as total')
        )
        ->where('intended_account_id', 1)
        ->where('created_at', '>=', $startDate);

        // إضافة تاريخ النهاية إذا كانت الفترة هي هذا الأسبوع أو هذا الشهر أو هذه السنة
        if (in_array($period, ['this_week', 'this_month', 'this_year'])) {
            $totalRevenueQuery->where('created_at', '<=', $endDate);
            $platformRevenueQuery->where('created_at', '<=', $endDate);

            // إضافة كل الأيام/الشهور في الفترة المحددة حتى لو لم تكن هناك معاملات
            $dates = [];
            $currentDate = $startDate->copy();

            if ($period == 'this_year') {
                // إضافة كل الشهور في السنة الحالية
                while ($currentDate->lte($endDate)) {
                    $dates[] = $currentDate->format('Y-m');
                    $currentDate->addMonth();
                }
            } else {
                // إضافة كل الأيام في الأسبوع أو الشهر الحالي
                while ($currentDate->lte($endDate)) {
                    $dates[] = $currentDate->format('Y-m-d');
                    $currentDate->addDay();
                }
            }
        }

        $totalRevenue = $totalRevenueQuery->groupBy('period')->orderBy('period')->get();

        // إذا كانت الفترة هي هذا الأسبوع أو هذا الشهر أو هذه السنة، تأكد من وجود كل الأيام/الشهور
        if (in_array($period, ['this_week', 'this_month', 'this_year'])) {
            $totalRevenueMap = $totalRevenue->pluck('total', 'period')->toArray();

            if ($period == 'this_year') {
                // تنسيق البيانات للسنة الحالية (شهريًا)
                $totalRevenue = collect($dates)->map(function ($date) use ($totalRevenueMap) {
                    return [
                        'period' => $date,
                        'total' => $totalRevenueMap[$date] ?? 0,
                        'month' => Carbon::createFromFormat('Y-m', $date)->format('M'),
                        'year' => Carbon::createFromFormat('Y-m', $date)->format('Y')
                    ];
                });

            } else {
                // تنسيق البيانات للأسبوع أو الشهر الحالي (يوم{})
                $totalRevenue = collect($dates)->map(function ($date) use ($totalRevenueMap) {
                    return [
                        'period' => $date,
                        'total' => $totalRevenueMap[$date] ?? 0,
                        'day' => Carbon::parse($date)->format('d'),
                        'month' => Carbon::parse($date)->format('M'),
                        'weekday' => Carbon::parse($date)->format('D')
                    ];
                });
            }
        }

        return [
            'period' => $period,
            'total_revenue' => $totalRevenue,
        ];
    }
    public function getRatingStatsInst($period = 'monthly')
    {
        $now = Carbon::now();

        switch ($period) {
            case 'daily':
                $startDate = $now->copy()->subDays(30);
                $groupBy = 'date';
                $dateFormat = '%Y-%m-%d';
                break;
            case 'weekly':
                $startDate = $now->copy()->subWeeks(12);
                $groupBy = 'week';
                $dateFormat = '%Y-%u';
                break;
            case 'yearly':
                $startDate = $now->copy()->subYears(5);
                $groupBy = 'year';
                $dateFormat = '%Y';
                break;
            case 'monthly':
            default:
                $startDate = $now->copy()->subMonths(12);
                $groupBy = 'month';
                $dateFormat = '%Y-%m';
                break;
        }

        $ratingQuery = Rate::select(
            DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
            DB::raw('AVG(rate) as average_rating')
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy('period')
        ->orderBy('period');

        $averageRatings = $ratingQuery->get();

        return [
            'period' => $period,
            'average_ratings' => $averageRatings,
        ];
    }
    public function getCourseDetails($courseId)
    {
        $course = Course::withCount([
            'students', // علاقة many-to-many
            'comments', // علاقة التعليقات
            'lessons', // علاقة الدروس
            'completions as completed_students_count' => function ($query) {
                $query->where('status', 'completed');
            }
        ])
        ->with([
            'transactions' => function ($q) {
                $q->select('course_id', DB::raw('SUM(amount) as total_revenue'))->groupBy('course_id');
            },
            'rates' => function ($q) {
                $q->select('course_id', DB::raw('AVG(rate) as average_rating'))->groupBy('course_id');
            }
        ])
        ->findOrFail($courseId);

        return [
            'course_id' => $course->id,
            'title' => $course->title,
            'students_count' => $course->students_count,
            'comments_count' => $course->comments_count,
            'lessons_count' => $course->lessons_count,
            'completed_students_count' => $course->completed_students_count,
            'total_revenue' => $course->transactions->first()->total_revenue ?? 0,
            'average_rating' => round($course->rates->first()->average_rating ?? 0, 2),
        ];
    }
public function getOverallCourseRatings()
{
    $instructorId = auth()->id();

    $courses = Course::where('instructor_id', $instructorId)->pluck('id');

    $averageRatings = Rate::whereIn('course_id', $courses)
        ->select('course_id', DB::raw('AVG(rate) as average_rating'))
        ->groupBy('course_id')
        ->get();

    return $averageRatings;
}
}
