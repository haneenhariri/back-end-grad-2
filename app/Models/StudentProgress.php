<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProgress extends Model
{
    use HasFactory;
    
    protected $table = 'student_progress';
    
    protected $fillable = [
        'user_id',
        'course_id',
        'lesson_id',
        'completed',
        'progress_percentage'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}