<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'question',
        'options',
        'correct_answer',
        'mark',
        'type'
    ];
    protected $casts = [
        'options' => 'json'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }
        public function answeredUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_answers')
            ->using(UserAnswer::class)
            ->withPivot(['answer', 'mark', 'id'])
            ->withTimestamps();
    }
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'user_answers')
            ->withPivot('answer', 'mark','id')
            ->withTimestamps();
    }
}
