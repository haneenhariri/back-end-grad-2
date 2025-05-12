<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class File extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'lesson_id',
        'path',
        'origin_name',
        'extension',
        'type'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "File No. {$this->id} has been {$eventName}.")
            ->useLogName('file');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
