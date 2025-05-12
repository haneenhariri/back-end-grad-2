<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $showAllTranslations = $request->query('all_translations') == true;

        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'title' => $this->whenNotNull(
                $showAllTranslations ? $this->getTranslations('title') : $this->getTranslation('title', app()->getLocale())
            ),
            'description' => $this->whenNotNull(
                $showAllTranslations ? $this->getTranslations('description') : $this->getTranslation('description', app()->getLocale())
            ),
            'course' => new CourseResource($this->whenLoaded('course')),
            'files'=>$this->whenLoaded('files'),
            'comments'=>CommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
