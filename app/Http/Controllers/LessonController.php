<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lesson\storeRequest;
use App\Http\Requests\Lesson\UpdateRequest;
use App\Http\Resources\LessonResource;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(storeRequest $request)
    {
        Lesson::create($request->validated());
        return self::success();
    }

    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson)
    {
        return self::success(new LessonResource($lesson->load(['files','comments.replies','comments.user'])));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Lesson $lesson)
    {
        $lesson->update(array_filter($request->validated()));
        return self::success(new LessonResource($lesson));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        $lesson->delete();
        return self::success(null, 'deleted successfully');
    }
}
