<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'name'=>$this->name,
            'category_id'=>$this->whenNotNull($this->category_id),
            'main_category'=>new CategoryResource($this->whenLoaded('mainCategory')),
            'sub_category'=>CategoryResource::collection($this->whenLoaded('subCategory'))
        ];
    }
}
