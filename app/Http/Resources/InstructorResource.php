<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'education' => $this->education,
            'specialization' => $this->specialization,
            'summery' => $this->summery,
            'cv' => $this->cv,
            'user' => new UserResource($this->whenLoaded('user')),
            'courses' => new CourseResource($this->whenLoaded('courses')),
        ];
    }
}
