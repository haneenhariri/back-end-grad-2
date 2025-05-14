<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMultilingualRequest extends FormRequest
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
            // بيانات الكورس الأساسية
            'duration' => ['nullable', 'numeric', 'min:0'],
            'level' => ['nullable', Rule::in(['beginner', 'intermediate', 'advance'])],
            'title' => ['nullable', 'array'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'title.ar' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'cover' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif'],
            'video_preview' => ['nullable', 'file', 'mimes:mp4,mov,avi'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'course_language' => ['nullable', Rule::in(['arabic', 'english', 'both'])],

            // الدروس
            'lessons' => ['nullable', 'array'],
            'lessons.*.id' => ['nullable', 'integer', 'exists:lessons,id'],
            'lessons.*.title' => ['nullable', 'array'],
            'lessons.*.title.en' => ['nullable', 'string', 'max:255'],
            'lessons.*.title.ar' => ['nullable', 'string', 'max:255'],
            'lessons.*.description' => ['nullable', 'array'],
            'lessons.*.description.en' => ['nullable', 'string'],
            'lessons.*.description.ar' => ['nullable', 'string'],
            'lessons.*.duration' => ['nullable', 'numeric', 'min:0'],
            'lessons.*.video_url' => ['nullable', 'string', 'url'],
            'lessons.*.order' => ['nullable', 'integer', 'min:0'],

            // ملفات الدروس
            'lessons.*.files' => ['nullable', 'array'],
            'lessons.*.files.*.id' => ['nullable', 'integer', 'exists:files,id'],
            'lessons.*.files.*.type' => ['nullable', 'string'],
            'lessons.*.files.*.path' => ['nullable', 'file'],

            // الدروس والملفات المحذوفة
            'deleted_lessons' => ['nullable', 'array'],
            'deleted_lessons.*' => ['integer', 'exists:lessons,id'],
            'deleted_files' => ['nullable', 'array'],
            'deleted_files.*' => ['integer', 'exists:files,id'],
        ];
    }
}
