<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class updateRequest extends FormRequest
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
            'name' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'profile_picture' => ['file', 'mimes:png,jpg,jpeg', 'nullable'],
            'role' => ['nullable', 'exists:roles,name'],
            'instructor' => ['array', 'nullable'],
            'instructor.education' => ['nullable', 'string'],
            'instructor.specialization' => ['nullable', 'string'],
            'instructor.summery' => ['string','nullable'],
            'instructor.cv' => ['nullable', 'file'],
        ];
    }
}
