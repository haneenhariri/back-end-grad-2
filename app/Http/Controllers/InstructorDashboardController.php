<?php

namespace App\Http\Controllers;

use App\Services\InstructorDashboardService;
use Illuminate\Http\Request;


class InstructorDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(InstructorDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function getGeneralStats()
    {
        $stats = $this->dashboardService->getStats();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
    public function getOverallCourseRatings(Request $request)
    {
        $period = $request->input('period', 'monthly'); // default: monthly
        $data = $this->dashboardService->getOverallCourseRatings($period);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    public function getCourseStats($courseId)
    {
        $data = $this->dashboardService->getCourseStats($courseId);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    public function getCourseRevenue($courseId, Request $request)
    {
        $period = $request->input('period', 'monthly'); // default
        $data = $this->dashboardService->getCourseRevenueStats($courseId, $period);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}

