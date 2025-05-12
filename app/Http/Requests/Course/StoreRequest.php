<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
            'duration' => ['required', 'numeric'],
            'level' => ['required', Rule::in(['beginner', 'intermediate', 'advance'])],
            'title' => ['required', 'array'],
            'title.en' => ['required_without:title.ar', 'string'],
            'title.ar' => ['required_without:title.en', 'string'],
            'description' => ['nullable', 'array'],
            'description.en' => ['string'],
            'description.ar' => ['string'],
            'price' => ['required', 'numeric'],
            'cover' => ['file'],
            'sub_category_id'=>['required','exists:categories,id'],
            'course_language'=>['required',Rule::in(['english','arabic'])],
            'lessons'=>['required','array'],
            'lessons.*.title'=>['array','required'],
            'lessons.*.title.en'=>['required_without:lessons.*.title.ar','string'],
            'lessons.*.title.ar'=>['required_without:lessons.*.title.en','string'],
            'lessons.*.description'=>['nullable','array'],
            'lessons.*.description.en'=>['string'],
            'lessons.*.description.ar'=>['string'],
            'lessons.*.files'=>['required','array'],
            'lessons.*.files.*.path'=>['required','file'],
            'lessons.*.files.*.type'=>['required',Rule::in(['video', 'file'])],
        ];
    }
}
