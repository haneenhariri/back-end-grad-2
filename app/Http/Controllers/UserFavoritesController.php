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
        $favorites = auth()->user()->favoriteCourses()->select([
            'courses.id',
            'level',
            'title',
            'sub_category_id'])->with('category:id,name')->get();
        return self::success(CourseResource::collection($favorites));
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
