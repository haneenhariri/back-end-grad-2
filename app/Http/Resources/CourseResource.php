<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // تحديد اللغة من الطلب
        $locale = $request->query('lang') ?? $request->header('Accept-Language') ?? app()->getLocale();

        // تحديد ما إذا كان يجب عرض جميع الترجمات
        $showAllTranslations = $request->query('all_translations') == true;

        return [
            'id' => $this->whenNotNull($this->id),
            'instructor_id' => $this->whenNotNull($this->instructor_id),
            'duration' => $this->whenNotNull($this->duration),
            'level' => $this->whenNotNull($this->level),
            'title' => $this->whenNotNull(
                $showAllTranslations ? $this->getTranslations('title') : $this->getTranslation('title', $locale)
            ),
            'description' => $this->whenNotNull(
                $showAllTranslations ? $this->getTranslations('description') : $this->getTranslation('description', app()->getLocale())
            ),
            'price' => $this->whenNotNull($this->price),
            'cover' => $this->whenNotNull($this->cover),
            'rating' => $this->whenNotNull($this->rating),
            'sub_category_id' => $this->whenNotNull($this->sub_category_id),
            'status' => $this->whenNotNull($this->status),
            'course_language' => $this->whenNotNull($this->getTranslatedLanguage()),
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
            'main_category_name' => $this->category->mainCategory->name ?? null,

            'instructor' => $this->whenLoaded('instructor', function () {
                return optional($this->instructor)->name;
            }),
            'sub_category' => new CategoryResource($this->whenLoaded('category')),
            'lessons' => LessonResource::collection($this->whenLoaded('lessons')),
            'rates' => $this->whenLoaded('rates'),
        ];
    }
}


