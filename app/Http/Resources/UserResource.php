<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'profile_picture' => $this->profile_picture,
            $this->mergeWhen($this->hasRole('instructor'),
                new InstructorResource(optional($this->instructor))
            ),
            'account' => $this->whenLoaded('account'),
            'courses' => $this->whenLoaded('courses', fn() => $this->courses,
                $this->whenLoaded('coursesForInstructor', fn() => $this->coursesForInstructor)
            ),
            'role' => $this->whenLoaded('roles', fn() => optional($this->roles->first())->name),
            'questions'=> QuestionResource::collection($this->whenLoaded('questions'))
        ];
    }
}
