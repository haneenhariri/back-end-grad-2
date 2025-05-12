<?php

namespace App\Http\Requests\User;

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
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
            'profile_picture' => ['file', 'mimes:png,jpg,jpeg', 'nullable'],
            'role' => ['required', 'exists:roles,name'],
            'instructor' => ['array', 'required_if:role,instructor'],
            'instructor.education' => ['required_with:instructor', 'string'],
            'instructor.specialization' => ['required_with:instructor', 'string'],
            'instructor.summery' => ['string'],
            'instructor.cv' => ['required', 'file'],
        ];
    }
}
