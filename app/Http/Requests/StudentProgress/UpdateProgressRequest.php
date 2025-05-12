<?php

namespace App\Http\Requests\StudentProgress;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lesson_id' => ['required', 'exists:lessons,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'completed' => ['boolean'],
            'progress_percentage' => ['integer', 'min:0', 'max:100']
        ];
    }
}