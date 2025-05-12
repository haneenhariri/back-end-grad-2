<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'lesson_id' => $this->lesson_id,
            'comment_id' => $this->comment_id,
            'content' => $this->content,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'profile_picture' => $this->user->profile_picture,
            ],
            'lesson' => new LessonResource($this->whenLoaded('lesson')),
            'parentComment' => new CommentResource($this->whenLoaded('parentComment')),
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
        ];
    }
}

