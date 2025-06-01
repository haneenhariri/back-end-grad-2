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
}

