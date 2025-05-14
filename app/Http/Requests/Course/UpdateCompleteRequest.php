<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // سيتم التحقق من الصلاحية في المتحكم
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
            'cover' => ['nullable', 'file', 'image', 'max:5120'], // 5MB max
            'video_preview' => ['nullable', 'file', 'mimes:mp4,mov,avi', 'max:102400'], // 100MB max
            'category_id' => ['nullable', 'exists:categories,id'],
            'course_language' => ['nullable', Rule::in(['english', 'arabic'])],

            // الدروس
            'lessons' => ['nullable', 'array'],
            'lessons.*.id' => ['nullable', 'integer', 'exists:lessons,id,course_id,' . $this->route('course')->id],
            'lessons.*.title' => ['required_without:lessons.*.is_deleted', 'array'],
            'lessons.*.title.en' => ['nullable', 'string', 'max:255'],
            'lessons.*.title.ar' => ['nullable', 'string', 'max:255'],
            'lessons.*.description' => ['nullable', 'array'],
            'lessons.*.description.en' => ['nullable', 'string'],
            'lessons.*.description.ar' => ['nullable', 'string'],
            'lessons.*.order' => ['nullable', 'integer', 'min:1'],
            'lessons.*.duration' => ['nullable', 'numeric', 'min:0'],
            'lessons.*.video_url' => ['nullable', 'string', 'url'],
            'lessons.*.is_deleted' => ['nullable', 'boolean'],

            // ملفات الدروس
            'lessons.*.files' => ['nullable', 'array'],
            'lessons.*.files.*.id' => ['nullable', 'integer', 'exists:files,id'],
            'lessons.*.files.*.path' => ['nullable', 'file', 'max:102400'], // 100MB max
            'lessons.*.files.*.type' => ['required_without:lessons.*.files.*.is_deleted', Rule::in(['video', 'file', 'document'])],
            'lessons.*.files.*.is_deleted' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'duration.numeric' => 'مدة الكورس يجب أن تكون رقمًا',
            'duration.min' => 'مدة الكورس يجب أن تكون أكبر من أو تساوي صفر',
            'level.in' => 'مستوى الكورس يجب أن يكون أحد القيم التالية: مبتدئ، متوسط، متقدم',
            'price.numeric' => 'سعر الكورس يجب أن يكون رقمًا',
            'price.min' => 'سعر الكورس يجب أن يكون أكبر من أو يساوي صفر',
            'discount_price.numeric' => 'سعر الخصم يجب أن يكون رقمًا',
            'discount_price.min' => 'سعر الخصم يجب أن يكون أكبر من أو يساوي صفر',
            'discount_price.lte' => 'سعر الخصم يجب أن يكون أقل من أو يساوي سعر الكورس',
            'cover.image' => 'صورة الغلاف يجب أن تكون صورة',
            'cover.max' => 'حجم صورة الغلاف يجب أن لا يتجاوز 5 ميجابايت',
            'video_preview.mimes' => 'فيديو المعاينة يجب أن يكون من نوع mp4 أو mov أو avi',
            'video_preview.max' => 'حجم فيديو المعاينة يجب أن لا يتجاوز 100 ميجابايت',
            'category_id.exists' => 'التصنيف المحدد غير موجود',
            'course_language.in' => 'لغة الكورس يجب أن تكون إنجليزية أو عربية',

            'lessons.*.id.exists' => 'الدرس المحدد غير موجود أو لا ينتمي لهذا الكورس',
            'lessons.*.title.required_without' => 'عنوان الدرس مطلوب',
            'lessons.*.order.integer' => 'ترتيب الدرس يجب أن يكون رقمًا صحيحًا',
            'lessons.*.order.min' => 'ترتيب الدرس يجب أن يكون أكبر من أو يساوي 1',
            'lessons.*.duration.numeric' => 'مدة الدرس يجب أن تكون رقمًا',
            'lessons.*.duration.min' => 'مدة الدرس يجب أن تكون أكبر من أو تساوي صفر',
            'lessons.*.files.*.path.max' => 'حجم ملف الدروس يجب أن لا يتجاوز 100 ميجابايت',
        ];
    }
}

