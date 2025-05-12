<?php

namespace App\Http\Requests\Rate;

use Illuminate\Foundation\Http\FormRequest;

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
            'rate'=>['required_without:review','numeric','between:0,5'],
            'review'=>['required_without:rate','string']
        ];
    }
}
