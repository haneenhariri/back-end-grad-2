<?php

namespace App\Http\Controllers;

use App\Http\Requests\Course\ChangeStatusRequest;
use App\Http\Requests\Course\StoreRequest;
use App\Http\Requests\Course\UpdateRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Services\CourseService;

class CourseController extends Controller
{
    protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Course::where('status', 'accepted')->with(['instructor','category.mainCategory'])->latest()->get();
        return self::success(CourseResource::collection($data));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $this->courseService->store($request->validated());
        return self::success();
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        return self::success(new CourseResource($course->load(['rates', 'instructor', 'category.mainCategory', 'lessons.files'])));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Course $course)
    {
        $course = $this->courseService->update($course, $request->validated());
        return self::success(new CourseResource($course));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        $course->delete();
        return self::success(null, 'deleted successfully');
    }

    public function changeStatus(ChangeStatusRequest $request, Course $course)
    {
        $course->update(['status' => $request->status]);
        return self::success(null, 'updated status successfully');
    }

    public function pendingCourse()
    {
        $courses = Course::where('status', 'pending')->latest()->get();
        return self::success(CourseResource::collection($courses));
    }
}
