<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentProgress\UpdateProgressRequest;
use App\Services\StudentProgressService;
use Illuminate\Http\Request;

class StudentProgressController extends Controller
{
    protected $progressService;

    public function __construct(StudentProgressService $progressService)
    {
        $this->progressService = $progressService;
    }

    public function updateProgress(UpdateProgressRequest $request)
    {
        $progress = $this->progressService->updateProgress($request->validated());
        return self::success($progress);
    }

    public function getCourseProgress(Request $request, $courseId)
    {
        $userId = auth()->id();
        $progress = $this->progressService->getUserCourseProgress($userId, $courseId);
        return self::success($progress);
    }

    public function getAllCoursesProgress()
    {
        $userId = auth()->id();
        $courses = auth()->user()->courses;

        $progressData = [];
        foreach ($courses as $course) {
            $progressData[] = [
                'course' => $course,
                'progress' => $this->progressService->calculateCourseProgress($userId, $course->id)
            ];
        }

        return self::success($progressData);
    }
}

