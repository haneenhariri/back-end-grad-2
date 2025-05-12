<?php

namespace App\Http\Requests\Question;

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
            'course_id'=>['required','exists:courses,id'],
            'question'=>['required','string'],
            'options'=>['nullable','array'],
            'options.*'=>['required_with:options','string'],
            'correct_answer'=>['nullable','string'],
            'mark'=>['required','numeric'],
            'type'=>['required',Rule::in(['multipleChoice','code'])]
        ];
    }
}
