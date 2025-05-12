<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'name' => $this->name,
            'email' => $this->email,
            'education' => $this->education,
            'specialization' => $this->specialization,
            'summery' => $this->summery,
            'cv' => $this->cv,
            'status' => $this->status
        ];
    }
}
