<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class EditProfileRequest extends FormRequest
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
            'name' => ['string', 'nullable'],
            'email' => ['email', 'unique:users,email', 'nullable'],
            'profile_picture' => ['file', 'mimes:png,jpg,jpeg', 'nullable'],
            'education' => ['string', 'nullable'],
            'specialization' => ['string', 'nullable'],
            'summery' => ['string', 'nullable']
        ];
    }
}
