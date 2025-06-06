<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicCourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
{
    return [
        'id' => $this->id,
        'title' => $this->getTranslation('title', app()->getLocale()),
        'description' => $this->getTranslation('description', app()->getLocale()),
        'duration' => $this->duration,
        'level' => $this->level,
        'level_translated' => __('enums.level.' . $this->level),
        'price' => $this->price,
        'cover' => $this->cover,
        'course_language' => $this->course_language,
        'course_language_translated' => __('enums.course_language.' . $this->course_language),

        'instructor' => [
            'id' => $this->instructor->id,
            'name' => $this->instructor->name,
            'profile_picture' => $this->instructor->profile_picture,
        ],

        'category' => $this->category ? [
            'id' => $this->category->id,
            'name' => $this->category->name,
        ] : null,

        'rates' => $this->rates->map(function ($rate) {
            return [
                'id' => $rate->id,
                'rate' => $rate->rate,
                'review' => $rate->review,
                'user' => [
                    'id' => $rate->user->id,
                    'name' => $rate->user->name,
                    'profile_picture' => $rate->user->profile_picture,
                ]
            ];
        }),
        
        'lessons' => $this->whenLoaded('lessons', function() {
                return $this->lessons->map(function ($lesson) {
                    $lessonData = [
                        'id' => $lesson->id,
                        'title' => $lesson->getTranslation('title', app()->getLocale()),
                    ];

                    // التحقق من وجود علاقة الملفات وإضافتها إذا كانت موجودة
                    if ($lesson->relationLoaded('files')) {
                        $lessonData['files'] = $lesson->files->map(function ($file) {
                            return [
                                'id' => $file->id,
                                'path' => $file->path,
                                'origin_name' => $file->origin_name,
                                'extension' => $file->extension,
                                'type' => $file->type
                            ];
                        });
                    }

                    return $lessonData;
                });
            }),

        'created_at' => $this->created_at,
    ];
}
}
