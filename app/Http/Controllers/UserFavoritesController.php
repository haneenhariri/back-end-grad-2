<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;

class UserFavoritesController extends Controller
{
    public function toggle($courseId)
    {
        auth()->user()->favoriteCourses()->toggle($courseId);
        return self::success();
    }

    public function favoritesForUser()
    {
        try {
            $userId = auth()->id();
            $lang = request()->query('lang') ?? request()->header('Accept-Language') ?? app()->getLocale();
            app()->setLocale($lang);

            // استعلام محسن مع معلومات المدرس
            $favorites = \DB::table('user_favorites')
                ->join('courses', 'user_favorites.course_id', '=', 'courses.id')
                ->leftJoin('users as instructors', 'courses.instructor_id', '=', 'instructors.id')
                ->leftJoin('rates', 'courses.id', '=', 'rates.course_id')
                ->where('user_favorites.user_id', $userId)
                ->select(
                    'courses.id',
                    'courses.title',
                    'courses.price',
                    'courses.cover',
                    'instructors.name as instructor_name',
                    'instructors.profile_picture as instructor_profile',
                    \DB::raw('AVG(rates.rate) as rating')
                )
                ->groupBy(
                    'courses.id',
                    'courses.title',
                    'courses.price',
                    'courses.cover',
                    'instructors.name',
                    'instructors.profile_picture'
                )
                ->get();

            // تحويل البيانات إلى التنسيق المطلوب
            $formattedFavorites = $favorites->map(function($course) use ($lang) {
                $title = $course->title;
                // إذا كان العنوان مخزناً كـ JSON (للترجمات)
                if (is_string($title) && $this->isJson($title)) {
                    $translations = json_decode($title, true);
                    $title = $translations[$lang] ?? $translations[array_key_first($translations)];
                }

                return [
                    'id' => $course->id,
                    'title' => $title,
                    'price' => $course->price,
                    'cover' => $course->cover,
                    'instructor_name' => $course->instructor_name,
                    'rating' => round($course->rating, 1) ?? 0
                ];
            });

            return self::success($formattedFavorites);
        } catch (\Exception $e) {
            \Log::error('Error fetching favorites', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return self::error('خطأ في قاعدة البيانات: ' . $e->getMessage());
        }
    }

    // دالة مساعدة للتحقق مما إذا كانت السلسلة عبارة عن JSON صالح
    private function isJson($string) {
        if (!is_string($string)) return false;
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }


    public function recommendedCourses()
    {
        $user = Auth::user();
        $mainCategoryIds = $user->favoriteCourses()->with('category.mainCategory')
            ->get()
            ->pluck('category.mainCategory.id')
            ->unique();

        $courses = Course::where('status', 'accepted')
            ->when($mainCategoryIds->isNotEmpty(), function ($query) use ($mainCategoryIds) {
                $query->whereHas('category', fn($q) => $q->whereIn('category_id', $mainCategoryIds));
            })
            ->with('instructor')
            ->latest()
            ->get();

        return self::success(CourseResource::collection($courses));
    }
}









