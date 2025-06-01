<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    protected $table = 'user_answers';
    
    protected $fillable = [
        'user_id',
        'question_id',
        'answer',
        'mark'
    ];

    protected $casts = [
        'mark' => 'float'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // علاقة غير مباشرة مع الكورس عبر السؤال
    public function course()
    {
        return $this->hasOneThrough(
            Course::class,
            Question::class,
            'id', // Foreign key on questions table
            'id', // Foreign key on courses table
            'question_id', // Local key on user_answers table
            'course_id' // Local key on questions table
        );
    }
}