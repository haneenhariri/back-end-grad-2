<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseMultilingualResource extends JsonResource
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
            'instructor_id' => $this->instructor_id,
            'duration' => $this->duration,
            'level' => $this->level,
            'level_translated' => [
                'en' => __('enums.level.' . $this->level, [], 'en'),
                'ar' => __('enums.level.' . $this->level, [], 'ar')
            ],
            'title' => $this->getTranslations('title'),
            'description' => $this->getTranslations('description'),
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'cover' => $this->cover,
            'video_preview' => $this->video_preview,
            'sub_category_id' => $this->sub_category_id,
            'status' => $this->status,
            'course_language' => $this->course_language,
            'course_language_translated' => [
                'en' => __('enums.course_language.' . $this->course_language, [], 'en'),
                'ar' => __('enums.course_language.' . $this->course_language, [], 'ar')
            ],

            'instructor' => $this->whenLoaded('instructor', function () {
                return [
                    'id' => $this->instructor->id,
                    'name' => $this->instructor->name,
                    'email' => $this->instructor->email,
                    'avatar' => $this->instructor->avatar
                ];
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


            'lessons' => $this->whenLoaded('lessons', function() {
                return $this->lessons->map(function ($lesson) {
                    $lessonData = [
                        'id' => $lesson->id,
                        'title' => $lesson->getTranslations('title'),
                        'description' => $lesson->getTranslations('description'),
                        'duration' => $lesson->duration,
                        'video_url' => $lesson->video_url,
                        'order' => $lesson->order,
                    ];

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
                        'comment' => $rate->comment,
                        'user' => [
                            'id' => $rate->user->id,
                            'name' => $rate->user->name,
                            'avatar' => $rate->user->avatar
                        ]
                    ];
                });
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
