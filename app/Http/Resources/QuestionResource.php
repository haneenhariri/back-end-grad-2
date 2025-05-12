<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'course_id' => $this->course_id,
            'course'=> new CourseResource($this->whenLoaded('course')) ,
            'question' => $this->question,
            'options' => $this->options,
            'correct_answer' => $this->correct_answer,
            'mark' => $this->mark,
            'type' => $this->type,

            'user_answer' => $this->whenPivotLoaded('user_answers', function () {
                return [
                    'id'=>$this->pivot->id,
                    'answer' => $this->pivot->answer,
                    'mark' => $this->pivot->mark,
                ];
            }),
        ];
    }
}
