<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CourseUser extends Pivot
{
    use LogsActivity;

    protected $table = 'course_user';
    public $incrementing = true;
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'course_id'
    ];


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Course purchase record No. {$this->id} has been {$eventName}.")
            ->useLogName('buy course');

    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

}
