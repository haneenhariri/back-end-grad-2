<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\LogOptions as ActivityLogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Lesson extends Model
{
    use HasFactory,HasTranslations, LogsActivity;

    public $translatable = ['title','description'];
    protected $fillable = [
        'course_id',
        'title',
        'description'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useAttributeRawValues($this->translatable) // تسجيل القيم المترجمة كـ JSON
            ->setDescriptionForEvent(fn(string $eventName) => "Lesson No. {$this->id} has been {$eventName}.")
            ->useLogName('lesson');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function comments(){
        return $this->hasMany(Comment::class)->whereNull('comment_id');
    }
}
