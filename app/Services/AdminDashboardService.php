<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Course;
use App\Models\Rate;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**
     * الحصول على الإحصائيات العامة للمنصة
     */
    public function getGeneralStats()
    {
        $totalStudents = User::role('student')->count();
        $totalInstructors = User::role('instructor')->count();
        $totalCourses = Course::count();
        $acceptedCourses = Course::where('status', 'accepted')->count();
        $pendingCourses = Course::where('status', 'pending')->count();

        // إجمالي الإيرادات (مجموع المعاملات)
        $totalRevenue = Transaction::sum('amount');

        // تحقق من المعاملات في قاعدة البيانات
        // يمكنك طباعة المعاملات للتحقق من القيم
        \Log::info('Total transactions: ' . Transaction::count());
        \Log::info('Platform transactions: ' . Transaction::where('intended_account_id', 1)->count());
        \Log::info('Platform revenue: ' . Transaction::where('intended_account_id', 1)->sum('amount'));

        // إيرادات المنصة - تأكد من أن معرف حساب المنصة هو 1
        $platformAccountId = 1; // تأكد من أن هذا هو معرف حساب المنصة الصحيح
        $platformRevenue = Transaction::where('intended_account_id', $platformAccountId)->sum('amount');

        // متوسط تقييم الكورسات
        $averageRating = Rate::avg('rate') ?: 0;

        return [
            'total_students' => $totalStudents,
            'total_instructors' => $totalInstructors,
            'total_courses' => $totalCourses,
            'accepted_courses' => $acceptedCourses,
            'pending_courses' => $pendingCourses,
            'total_revenue' => $totalRevenue,
            'platform_revenue' => $platformRevenue,
            'average_rating' => $averageRating,
        ];
    }

    /**
     * الحصول على إحصائيات الإيرادات
     */
    public function getRevenueStats($period = 'monthly')
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
        $platformRevenue = $platformRevenueQuery->groupBy('period')->orderBy('period')->get();

        // إذا كانت الفترة هي هذا الأسبوع أو هذا الشهر أو هذه السنة، تأكد من وجود كل الأيام/الشهور
        if (in_array($period, ['this_week', 'this_month', 'this_year'])) {
            $totalRevenueMap = $totalRevenue->pluck('total', 'period')->toArray();
            $platformRevenueMap = $platformRevenue->pluck('total', 'period')->toArray();

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

                $platformRevenue = collect($dates)->map(function ($date) use ($platformRevenueMap) {
                    return [
                        'period' => $date,
                        'total' => $platformRevenueMap[$date] ?? 0,
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

                $platformRevenue = collect($dates)->map(function ($date) use ($platformRevenueMap) {
                    return [
                        'period' => $date,
                        'total' => $platformRevenueMap[$date] ?? 0,
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
            'platform_revenue' => $platformRevenue,
        ];
    }

    /**
     * الحصول على تقييمات الكورسات
     */
    public function getOverallCourseRatings($period = 'monthly')
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

        $baseQuery = Rate::select(
            DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
            DB::raw('AVG(rate) as average_rating'),
            DB::raw('COUNT(*) as count')
        )->where('created_at', '>=', $startDate);
        if (isset($endDate)) {
            $baseQuery->where('created_at', '<=', $endDate);
        }

        $results = $baseQuery->groupBy('period')->orderBy('period')->get();

        // توزيع التقييمات العامة (5 إلى 1 نجمة)
        $totalRatingsQuery = Rate::whereHas('course');  
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

    /**
     * الحصول على إحصائيات المستخدمين
     */
    public function getUserStats()
    {
        // عدد المستخدمين الجدد حسب الشهر
        $newUsersByMonth = User::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', Carbon::now()->subYear())
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        // توزيع المستخدمين حسب الدور
        $usersByRole = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('COUNT(*) as count'))
            ->groupBy('roles.name')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->name => $item->count];
            });

        // أكثر الطلاب نشاطًا (بناءً على عدد الكورسات المشتركين بها)
        $mostActivestudents = User::role('student')
            ->select('users.id', 'users.name', DB::raw('COUNT(course_user.course_id) as courses_count'))
            ->join('course_user', 'users.id', '=', 'course_user.user_id')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('courses_count')
            ->limit(10)
            ->get();

        // أكثر المدرسين نشاطًا (بناءً على عدد الكورسات التي قاموا بإنشائها)
        $mostActiveInstructors = User::role('instructor')
            ->select('users.id', 'users.name', DB::raw('COUNT(courses.id) as courses_count'))
            ->join('courses', 'users.id', '=', 'courses.instructor_id')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('courses_count')
            ->limit(10)
            ->get();

        return [
            'new_users_by_month' => $newUsersByMonth,
            'users_by_role' => $usersByRole,
            'most_active_students' => $mostActivestudents,
            'most_active_instructors' => $mostActiveInstructors,
        ];
    }

    /**
     * الحصول على إحصائيات الكورسات
     */
    public function getCourseStats()
    {
        // عدد الكورسات الجديدة حسب الشهر
        $newCoursesByMonth = Course::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', Carbon::now()->subYear())
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        // توزيع الكورسات حسب الفئة
        $coursesByCategory = Course::select('categories.name as category', DB::raw('COUNT(courses.id) as count'))
            ->join('categories', 'courses.category_id', '=', 'categories.id')
            ->groupBy('categories.name')
            ->orderByDesc('count')
            ->get();

        // أكثر الكورسات مبيعًا
        $topSellingCourses = Course::select('courses.id', 'courses.title', DB::raw('COUNT(course_user.user_id) as students_count'))
            ->join('course_user', 'courses.id', '=', 'course_user.course_id')
            ->groupBy('courses.id', 'courses.title')
            ->orderByDesc('students_count')
            ->limit(10)
            ->get();

        // متوسط عدد الدروس لكل كورس
        $averageLessonsPerCourse = Course::select(DB::raw('AVG(lessons_count) as average'))
            ->fromSub(function ($query) {
                $query->select('courses.id', DB::raw('COUNT(lessons.id) as lessons_count'))
                    ->from('courses')
                    ->leftJoin('lessons', 'courses.id', '=', 'lessons.course_id')
                    ->groupBy('courses.id');
            }, 'course_lessons')
            ->first()
            ->average;

        return [
            'new_courses_by_month' => $newCoursesByMonth,
            'courses_by_category' => $coursesByCategory,
            'top_selling_courses' => $topSellingCourses,
            'average_lessons_per_course' => $averageLessonsPerCourse,
        ];
    }

    /**
     * الحصول على أحدث المعاملات المالية
     */
    public function getLatestTransactions()
    {
        return Transaction::with(['account.user', 'intendedAccount.user', 'course'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'date' => $transaction->created_at,
                    'from' => optional($transaction->account)->user ? [
                        'id' => $transaction->account->user->id,
                        'name' => $transaction->account->user->name,
                    ] : null,
                    'to' => optional($transaction->intendedAccount)->user ? [
                        'id' => $transaction->intendedAccount->user->id,
                        'name' => $transaction->intendedAccount->user->name,
                    ] : null,
                    'course' => optional($transaction->course) ? [
                        'id' => $transaction->course->id,
                        'title' => $transaction->course->title,
                    ] : null,
                ];
            });
    }

    /**
     * الحصول على أحدث المستخدمين المسجلين
     */
    public function getLatestUsers()
    {
        return User::with('roles')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_picture' => $user->profile_picture,
                    'role' => optional($user->roles->first())->name,
                    'created_at' => $user->created_at,
                ];
            });
    }

    /**
     * الحصول على أحدث الكورسات المضافة
     */
    public function getLatestCourses()
    {
        return Course::with(['instructor', 'category'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'price' => $course->price,
                    'status' => $course->status,
                    'created_at' => $course->created_at,
                    'instructor' => optional($course->instructor) ? [
                        'id' => $course->instructor->id,
                        'name' => $course->instructor->name,
                    ] : null,
                    'category' => optional($course->category) ? [
                        'id' => $course->category->id,
                        'name' => $course->category->name,
                    ] : null,
                ];
            });
    }
}












