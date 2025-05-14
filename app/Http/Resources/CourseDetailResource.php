<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslations('title'), // عرض الترجمات لكل اللغات
            'description' => $this->getTranslations('description'), // عرض الترجمات لكل اللغات
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'duration' => $this->duration,
            'level' => $this->level,
            'level_translated' => [
                'en' => __('enums.level.' . $this->level, [], 'en'),
                'ar' => __('enums.level.' . $this->level, [], 'ar')
            ],
            'image' => $this->cover,
            'video_preview' => $this->video_preview,
            'status' => $this->status,
            'course_language' => $this->course_language,
            'course_language_translated' => [
                'en' => __('enums.course_language.' . $this->course_language, [], 'en'),
                'ar' => __('enums.course_language.' . $this->course_language, [], 'ar')
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // العلاقات
            'instructor' => $this->whenLoaded('instructor', function() {
                return [
                    'id' => $this->instructor->id,
                    'name' => $this->instructor->name,
                    'profile_picture' => $this->instructor->profile_picture,
                    'education' => optional($this->instructor->instructor)->education,
                    'specialization' => optional($this->instructor->instructor)->specialization,
                    'summary' => optional($this->instructor->instructor)->summery,
                ];
            }),

            'lessons' => $this->whenLoaded('lessons', function() {
                return $this->lessons->map(function ($lesson) {
                    $lessonData = [
                        'id' => $lesson->id,
                        'title' => $lesson->getTranslations('title'), // عرض الترجمات لكل اللغات
                        'description' => $lesson->getTranslations('description'), // عرض الترجمات لكل اللغات
                        'duration' => $lesson->duration,
                        'video_url' => $lesson->video_url,
                        'order' => $lesson->order,
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

            'rates' => $this->whenLoaded('rates', function() {
                return $this->rates->map(function ($rate) {
                    return [
                        'id' => $rate->id,
                        'rate' => $rate->rate,
                        'review' => $rate->review,
                        'created_at' => $rate->created_at,
                        'user' => [
                            'id' => $rate->user->id,
                            'name' => $rate->user->name,
                            'profile_picture' => $rate->user->profile_picture,
                        ],
                    ];
                });
            }),

            'category' => $this->whenLoaded('category', function() {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'main_category' => $this->category->mainCategory ? [
                        'id' => $this->category->mainCategory->id,
                        'name' => $this->category->mainCategory->name
                    ] : null
                ];
            }),

            'average_rating' => $this->whenLoaded('rates', function() {
                return $this->rates->avg('rate') ?? 0;
            }),

            'total_rates' => $this->whenLoaded('rates', function() {
                return $this->rates->count();
            }),

            'total_lessons' => $this->whenLoaded('lessons', function() {
                return $this->lessons->count();
            }),
        ];
    }
}


