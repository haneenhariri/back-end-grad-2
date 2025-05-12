<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id'=>$this->user_id,
            'question_id'=>$this->question_id,
            'answer'=>$this->answer,
            'mark'=>$this->mark,
            'user'=>new UserResource($this->whenLoaded('user')),
            'question'=>new UserResource($this->whenLoaded('question')),
        ];
    }
}
