<?php

namespace App\Http\Requests\Question;

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
            'question'=>['nullable','string'],
            'options'=>['nullable','array'],
            'options.*'=>['nullable','string'],
            'correct_answer'=>['nullable','string'],
            'mark'=>['nullable','numeric'],
            'type'=>['nullable',Rule::in(['multipleChoice','code'])]
        ];
    }
}
