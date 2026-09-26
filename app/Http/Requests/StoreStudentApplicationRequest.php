<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['student_name', 'guardian_name'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => preg_replace('/\s+/u', ' ', trim($this->input($field)))]);
            }
        }

        if (is_string($this->input('guardian_phone'))) {
            $phone = strtr($this->input('guardian_phone'), array_combine(
                preg_split('//u', '٠١٢٣٤٥٦٧٨٩۰۱۲۳۴۵۶۷۸۹', -1, PREG_SPLIT_NO_EMPTY),
                str_split('01234567890123456789'),
            ));
            $phone = preg_replace('/[\s().-]+/u', '', $phone);
            $this->merge(['guardian_phone' => preg_replace('/^00/', '+', $phone)]);
        }
    }

    public function rules(): array
    {
        return [
            'student_name' => ['bail', 'required', 'string', 'max:150', 'regex:/^[\x{0621}-\x{063A}\x{0641}-\x{065F}\x{0670}-\x{06D3}]+(?: [\x{0621}-\x{063A}\x{0641}-\x{065F}\x{0670}-\x{06D3}]+){2,}$/u'],
            'date_of_birth' => ['required', 'date_format:Y-m-d', 'before:today'],
            'educational_stage_id' => ['required', 'integer', Rule::exists('educational_stages', 'id')->where('is_active', true)],
            'guardian_name' => ['required', 'string', 'max:150'],
            'guardian_phone' => ['required', 'string', 'max:20', 'regex:/^(?:[0-9]{8}|\+?[1-9][0-9]{8,14})$/D', 'not_regex:/^0+$/D'],
            'guardian_email' => ['nullable', 'string', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'document' => ['bail', 'nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,image/jpeg,image/png', 'extensions:pdf,jpg,jpeg,png'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب.',
            'string' => 'يرجى إدخال :attribute كنص.',
            'max.string' => 'يجب ألا يتجاوز :attribute :max حرفاً.',
            'student_name.regex' => 'يرجى إدخال اسم الطالب الثلاثي باللغة العربية.',
            'date_of_birth.date_format' => 'يرجى إدخال تاريخ ميلاد صحيح.',
            'date_of_birth.before' => 'يجب أن يكون تاريخ الميلاد سابقاً لليوم.',
            'educational_stage_id.integer' => 'يرجى اختيار مرحلة تعليمية متاحة.',
            'educational_stage_id.exists' => 'يرجى اختيار مرحلة تعليمية متاحة.',
            'guardian_phone.regex' => 'يرجى إدخال رقم هاتف محلي من ٨ أرقام أو رقم دولي صحيح.',
            'guardian_phone.not_regex' => 'يرجى إدخال رقم هاتف صحيح.',
            'guardian_email.email' => 'يرجى إدخال بريد إلكتروني صحيح.',
            'document.file' => 'تعذر رفع المستند. يرجى اختيار الملف مجدداً.',
            'document.uploaded' => 'تعذر رفع المستند. الحد الأقصى لحجم الملف ٥ ميغابايت.',
            'document.max' => 'يجب ألا يتجاوز حجم المستند ٥ ميغابايت.',
            'document.mimes' => 'المستند يجب أن يكون PDF أو JPG أو JPEG أو PNG.',
            'document.mimetypes' => 'محتوى المستند غير مسموح. استخدم PDF أو JPG أو JPEG أو PNG.',
            'document.extensions' => 'امتداد المستند يجب أن يكون PDF أو JPG أو JPEG أو PNG.',
        ];
    }

    public function attributes(): array
    {
        return [
            'student_name' => 'اسم الطالب الثلاثي',
            'date_of_birth' => 'تاريخ الميلاد',
            'educational_stage_id' => 'المرحلة التعليمية',
            'guardian_name' => 'اسم ولي الأمر',
            'guardian_phone' => 'هاتف ولي الأمر',
            'guardian_email' => 'البريد الإلكتروني',
            'notes' => 'الملاحظات',
            'document' => 'المستند',
        ];
    }
}
