<?php

namespace App\Http\Controllers;

use App\Services\AdminDashboardService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(AdminDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
        $this->middleware('role:admin');
    }

    /**
     * الحصول على الإحصائيات العامة للمنصة
     */
    public function getGeneralStats()
    {
        // إضافة استعلام مباشر للتحقق من البيانات
        $platformTransactions = \App\Models\Transaction::where('intended_account_id', 1)->get();
        \Log::info('Platform transactions: ', $platformTransactions->toArray());

        $stats = $this->dashboardService->getGeneralStats();
        return self::success($stats);
    }

    /**
     * الحصول على إحصائيات الإيرادات
     */
    public function getRevenueStats(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $stats = $this->dashboardService->getRevenueStats($period);
        return self::success($stats);
    }

    /**
     * الحصول على تقييمات الكورسات
     */
    public function getOverallCourseRatings(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $data = $this->dashboardService->getOverallCourseRatings($period);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * الحصول على إحصائيات المستخدمين
     */
    public function getUserStats()
    {
        $stats = $this->dashboardService->getUserStats();
        return self::success($stats);
    }

    /**
     * الحصول على إحصائيات الكورسات
     */
    public function getCourseStats()
    {
        $stats = $this->dashboardService->getCourseStats();
        return self::success($stats);
    }

    /**
     * الحصول على أحدث المعاملات المالية
     */
    public function getLatestTransactions()
    {
        $transactions = $this->dashboardService->getLatestTransactions();
        return self::success($transactions);
    }

    /**
     * الحصول على أحدث المستخدمين المسجلين
     */
    public function getLatestUsers()
    {
        $users = $this->dashboardService->getLatestUsers();
        return self::success($users);
    }

    /**
     * الحصول على أحدث الكورسات المضافة
     */
    public function getLatestCourses()
    {
        $courses = $this->dashboardService->getLatestCourses();
        return self::success($courses);
    }
}

