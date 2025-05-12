<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\StudentProgress;
use Illuminate\Support\Facades\Auth;

class StudentProgressService
{
    public function updateProgress(array $data)
    {
        $userId = Auth::id();
        $lessonId = $data['lesson_id'];
        $courseId = $data['course_id'];
        $completed = $data['completed'] ?? false;
        $progressPercentage = $data['progress_percentage'] ?? 0;
        
        // Update or create progress record
        $progress = StudentProgress::updateOrCreate(
            ['user_id' => $userId, 'lesson_id' => $lessonId],
            [
                'course_id' => $courseId,
                'completed' => $completed,
                'progress_percentage' => $progressPercentage
            ]
        );
        
        // Calculate overall course progress
        $this->calculateCourseProgress($userId, $courseId);
        
        return $progress;
    }
    
    public function calculateCourseProgress($userId, $courseId)
    {
        $course = Course::findOrFail($courseId);
        $totalLessons = $course->lessons()->count();
        $completedLessons = StudentProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('completed', true)
            ->count();
        
        $overallProgress = $totalLessons > 0 
            ? round(($completedLessons / $totalLessons) * 100) 
            : 0;
            
        return [
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'overall_progress' => $overallProgress
        ];
    }
    
    public function getUserCourseProgress($userId, $courseId)
    {
        $progress = StudentProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->with('lesson')
            ->get();
            
        $overallProgress = $this->calculateCourseProgress($userId, $courseId);
        
        return [
            'lessons_progress' => $progress,
            'overall_progress' => $overallProgress
        ];
    }
}