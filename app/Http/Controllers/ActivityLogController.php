<?php

namespace App\Http\Controllers;

use App\Models\CourseUser;
use Spatie\Activitylog\Models\Activity;


class ActivityLogController extends Controller
{
    public function index()
    {
        $activities = Activity::query()
            ->with('causer')
            ->with([
                'subject' => function ($morphTo) {
                    $morphTo->morphWith([
                        CourseUser::class => ['course'], // فقط إذا كان الـ subject هو CourseUser، نحمل معه course
                    ]);
                }
            ])
            ->get();
        return self::success($activities);
    }
}
