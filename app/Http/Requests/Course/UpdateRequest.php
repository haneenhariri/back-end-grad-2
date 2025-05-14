<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'duration'=>['nullable','numeric'],
            'level' => ['nullable', Rule::in(['beginner', 'intermediate', 'advance'])],
            'title' => ['nullable','array'],
            'title.en' => ['string'],
            'title.ar' => ['string'],
            'description' => ['nullable', 'array'],
            'description.en' => ['string'],
            'description.ar' => ['string'],
            'price'=>['nullable','numeric'],
            'cover' => ['nullable', 'file'],
            'category_id'=>['nullable','exists:categories,id']
        ];
    }
}
