<?php

namespace App\Services;

use App\Models\Course;
use App\Models\File;
use App\Models\Lesson;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CourseService
{
    public function store(array $data)
    {
        DB::transaction(function () use ($data) {
            $data['instructor_id'] = auth()->user()->id;
            if (array_key_exists('cover', $data)) {
                $data['cover'] = Storage::disk('public')->put('/course-cover', $data['cover']);
            }
            $course = Course::create($data);
            foreach ($data['lessons'] as $lesson) {
                $lesson['course_id'] = $course->id;
                $newLesson = Lesson::create($lesson);
                foreach ($lesson['files'] as $file) {
                    $file['origin_name'] = $file['path']->getClientOriginalName();
                    $file['extension'] = $file['path']->getClientOriginalExtension();
                    $file['path'] = Storage::disk('public')->put('/lesson', $file['path']);
                    $file['lesson_id'] = $newLesson->id;
                    File::create($file);
                }
            }
        });
    }

    public function update(Course $course, array $data)
    {
        $data = array_filter($data);
        if (array_key_exists('cover',$data)) {
            $data['cover'] = (new FileService())->updatePhoto($data['cover'], $course->cover, 'course-cover');
        }
        $course->update($data);
        return $course;
    }
}
