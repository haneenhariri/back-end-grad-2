<?php

namespace App\Http\Controllers;

use App\Http\Requests\Course\ChangeStatusRequest;
use App\Http\Requests\Course\StoreRequest;
use App\Http\Requests\Course\UpdateRequest;
use App\Http\Requests\Course\UpdateMultilingualRequest;
use App\Http\Resources\CourseResource;
use App\Http\Resources\CourseMultilingualResource;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\Request;

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
        $course = $this->courseService->store($request->validated());
        return self::success([
            'message' => 'تم إضافة الكورس بنجاح',
            'course_id' => $course->id
        ]);    }

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
        try {
            // حذف السجلات المرتبطة يدوياً
            \DB::table('course_user')->where('course_id', $course->id)->delete();

            // حذف أي سجلات أخرى مرتبطة
            \DB::table('rates')->where('course_id', $course->id)->delete();
            \DB::table('transactions')->where('course_id', $course->id)->delete();
            \DB::table('user_favorites')->where('course_id', $course->id)->delete();
            \DB::table('student_progress')->where('course_id', $course->id)->delete();

            // حذف الكورس
            $course->delete();

            return self::success(null, 'تم حذف الكورس بنجاح');
        } catch (\Exception $e) {
            \Log::error('Error deleting course', [
                'course_id' => $course->id,
                'error' => $e->getMessage()
            ]);

            return self::error('حدث خطأ أثناء حذف الكورس: ' . $e->getMessage());
        }
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

    /**
     * عرض تفاصيل الكورس باللغتين
     *
     * @param \App\Models\Course $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function showMultilingual(Course $course)
    {
        try {
            $course->load(['instructor', 'category', 'lessons.files', 'rates.user']);
            return self::success(new CourseMultilingualResource($course));
        } catch (\Exception $e) {
            return self::error($e->getMessage());
        }
    }

    /**
     * عرض قائمة الكورسات باللغتين
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexMultilingual(Request $request)
    {
        try {
            $query = Course::query();

            // تطبيق الفلاتر
            if ($request->has('category_id')) {
                $query->where('sub_category_id', $request->category_id);
            }

            if ($request->has('level')) {
                $query->where('level', $request->level);
            }

            if ($request->has('course_language')) {
                $query->where('course_language', $request->course_language);
            }

            if ($request->has('instructor_id')) {
                $query->where('instructor_id', $request->instructor_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // البحث
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereRaw("JSON_EXTRACT(title, '$.en') LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("JSON_EXTRACT(title, '$.ar') LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("JSON_EXTRACT(description, '$.en') LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("JSON_EXTRACT(description, '$.ar') LIKE ?", ["%{$search}%"]);
                });
            }

            // الترتيب
            $sortBy = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $query->orderBy($sortBy, $sortDirection);

            // التحميل المسبق للعلاقات
            $query->with(['instructor', 'category']);

            // التقسيم إلى صفحات
            $perPage = $request->input('per_page', 10);
            $courses = $query->paginate($perPage);

            return self::success(
                CourseMultilingualResource::collection($courses),
                null,
                [
                    'pagination' => [
                        'total' => $courses->total(),
                        'per_page' => $courses->perPage(),
                        'current_page' => $courses->currentPage(),
                        'last_page' => $courses->lastPage(),
                    ]
                ]
            );
        } catch (\Exception $e) {
            return self::error($e->getMessage());
        }
    }
}




