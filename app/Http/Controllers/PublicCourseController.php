<?php

namespace App\Http\Controllers;
use App\Models\Course;

use Illuminate\Http\Request;
use App\Http\Resources\PublicCourseResource;
class PublicCourseController extends Controller
{
   public function show($id)
    {
        try {
            app()->setLocale(request('lang', 'ar'));

            $course = Course::with(['instructor', 'category', 'rates.user' , 'lessons'])
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => new PublicCourseResource($course)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Course not found or other error'
            ], 404);
        }
    }
}
