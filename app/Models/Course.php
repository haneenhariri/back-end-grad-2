<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Course extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    public $translatable = ['title', 'description'];

    protected $appends = [
//        'rating'
    ];

    protected $fillable = [
        'instructor_id',
        'duration',
        'level',
        'title',
        'description',
        'price',
        'cover',
        'sub_category_id',
        'status',
        'course_language'
    ];

    public function getTranslatedLevel(): string
    {
        return __('enums.level.' . $this->level);
    }

    public function getTranslatedLanguage(): string
    {
        return __('enums.course_language.' . $this->course_language);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useAttributeRawValues($this->translatable) // تسجيل القيم المترجمة كـ JSON
            ->setDescriptionForEvent(fn(string $eventName) => "Course No. {$this->id} has been {$eventName}.")
            ->useLogName('course');
    }


    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id', 'id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->using(CourseUser::class)->withTimestamps();
    }


    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    public function getRatingAttribute()
    {
        return $this->rates()->avg('rate');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'sub_category_id', 'id');
    }

    public function transaction()
    {
        return $this->hasMany(Transaction::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function usersPreferCourse()
    {
        return $this->belongsToMany(User::class, 'user_favorites')->withTimestamps();
    }

    public function progress()
    {
        return $this->hasMany(StudentProgress::class);
    }
    public function answers()
    {
        return $this->hasManyThrough(
            \App\Models\UserAnswer::class,     // النموذج النهائي: إجابات الطلاب
            \App\Models\Question::class,       // النموذج الوسيط: الأسئلة
            'course_id',                       // المفتاح الأجنبي في جدول الأسئلة (يشير للكورس)
            'question_id',                     // المفتاح الأجنبي في جدول الإجابات (يشير للسؤال)
            'id',                              // المفتاح المحلي في جدول الكورسات
            'id'                               // المفتاح المحلي في جدول الأسئلة
        );
    }
}

