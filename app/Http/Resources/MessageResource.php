<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'sender_id' => $this->sender_id,
            'received_id' => $this->received_id,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'sender' => $this->whenLoaded('sender', function() {
                return [
                    'id' => $this->sender->id,
                    'name' => $this->sender->name,
                    'profile_picture' => $this->sender->profile_picture
                ];
            }),
            'received' => $this->whenLoaded('recevied', function() {
                return [
                    'id' => $this->recevied->id,
                    'name' => $this->recevied->name,
                    'profile_picture' => $this->recevied->profile_picture
                ];
            }),
        ];
    }
}

