<?php

namespace App\Http\Requests\Lesson;

use Illuminate\Foundation\Http\FormRequest;

class storeRequest extends FormRequest
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
            'course_id'=>['required','exists:courses,id'],
            'title'=>['array','required'],
            'title.en'=>['required_without:lessons.*.title.ar','string'],
            'title.ar'=>['required_without:lessons.*.title.en','string'],
            'description'=>['nullable','array'],
            'description.en'=>['string'],
            'description.ar'=>['string'],
        ];
    }
}
