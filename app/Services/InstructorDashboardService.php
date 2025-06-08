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

            $totalRevenue = Transaction::where('intended_account_id', $accountId)
                ->join('courses', 'transactions.course_id', '=', 'courses.id')
                ->where('courses.instructor_id', $instructorId)
                ->sum('transactions.amount');
            $averageRating = Rate::avg('rate') ?: 0;

            // استعلام لحساب عدد الطلاب لكل كورس وترتيبهم
            $topCourses = Course::select('courses.id' , 'courses.title',
             DB::raw('(SELECT COUNT(*) FROM course_user WHERE course_user.course_id = courses.id) as students_count'),
             DB::raw('(SELECT AVG(rate) FROM rates WHERE rates.course_id = courses.id) as average_rating'),
                   DB::raw("(SELECT SUM(amount) FROM transactions 
                 WHERE transactions.course_id = courses.id 
                 AND transactions.intended_account_id = $accountId) as revenue"))
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
        $instructorId = auth()->id();
    $now = Carbon::now();

    switch ($period) {
        case 'daily':
            $startDate = $now->copy()->subDays(30);
            $dateFormat = '%Y-%m-%d';
            break;
        case 'weekly':
            $startDate = $now->copy()->subWeeks(12);
            $dateFormat = '%Y-%u';  // ISO week number
            break;
        case 'yearly':
            $startDate = $now->copy()->subYears(5);
            $dateFormat = '%Y';
            break;
        case 'this_week':
            $startDate = $now->copy()->startOfWeek();
            $endDate = $now->copy()->endOfWeek();
            $dateFormat = '%Y-%m-%d';
            break;
        case 'this_month':
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
            $dateFormat = '%Y-%m-%d';
            break;
        case 'this_year':
            $startDate = $now->copy()->startOfYear();
            $endDate = $now->copy()->endOfYear();
            $dateFormat = '%Y-%m';
            break;
        case 'monthly':
        default:
            $startDate = $now->copy()->subMonths(12);
            $dateFormat = '%Y-%m';
            break;
    }

    // الإيرادات الخاصة بالأستاذ
    $revenueQuery = Transaction::select(
        DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
        DB::raw('SUM(amount) as total')
    )
    ->where('created_at', '>=', $startDate)
    ->whereHas('course', function ($query) use ($instructorId) {
        $query->where('instructor_id', $instructorId);
    });

    if (isset($endDate)) {
        $revenueQuery->where('created_at', '<=', $endDate);
    }

    $revenue = $revenueQuery->groupBy('period')->orderBy('period')->get();

    // إضافة أيام/شهور مفقودة في حالة this_week / this_month / this_year
    if (in_array($period, ['this_week', 'this_month', 'this_year'])) {
        $dates = [];
        $currentDate = $startDate->copy();

        if ($period == 'this_year') {
            while ($currentDate->lte($endDate)) {
                $dates[] = $currentDate->format('Y-m');
                $currentDate->addMonth();
            }
        } else {
            while ($currentDate->lte($endDate)) {
                $dates[] = $currentDate->format('Y-m-d');
                $currentDate->addDay();
            }
        }

        $revenueMap = $revenue->pluck('total', 'period')->toArray();

        $revenue = collect($dates)->map(function ($date) use ($revenueMap, $period) {
            $parsed = Carbon::parse($date);
            $item = [
                'period' => $date,
                'total' => $revenueMap[$date] ?? 0,
            ];

            if ($period == 'this_year') {
                $item['month'] = $parsed->format('M');
                $item['year'] = $parsed->format('Y');
            } else {
                $item['day'] = $parsed->format('d');
                $item['month'] = $parsed->format('M');
                $item['weekday'] = $parsed->format('D');
            }

            return $item;
        });
    }

    return [
        'period' => $period,
        'total_revenue' => $revenue,
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
    public function getOverallCourseRatings($period = 'monthly')
    {
        $instructorId = auth()->id();
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

        $baseQuery = Rate::select(
            DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
            DB::raw('AVG(rate) as average_rating'),
            DB::raw('COUNT(*) as count')
        )->where('created_at', '>=', $startDate)
        ->whereHas('course', function ($query) use ($instructorId) {
            $query->where('instructor_id', $instructorId);
        });
        if (isset($endDate)) {
            $baseQuery->where('created_at', '<=', $endDate);
        }

        $results = $baseQuery->groupBy('period')->orderBy('period')->get();

        // توزيع التقييمات العامة (5 إلى 1 نجمة)
        $totalRatingsQuery = Rate::whereHas('course', function ($q) use ($instructorId) {
                $q->where('instructor_id', $instructorId);
            });        
        $distributionRaw = Rate::select('rate', DB::raw('COUNT(*) as count'))
            ->groupBy('rate')
            ->get()
            ->pluck('count', 'rate');
        $totalRatings = $totalRatingsQuery->count();

        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $distributionRaw[$i] ?? 0;
            $distribution[$i] = $totalRatings > 0 ? round(($count / $totalRatings) * 100) : 0;
        }
        $overallRating = $totalRatingsQuery->avg('rate');

        return [
            'period' => $period,
            'timeline' => $results, // تغير التقييمات عبر الزمن
            'overall_rating' => round($overallRating, 1),
            'rating_distribution' => $distribution
        ];
    }
}
