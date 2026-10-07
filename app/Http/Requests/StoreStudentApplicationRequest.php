<?php

namespace App\Http\Requests;

use App\Models\EducationalGrade;
use App\Models\EducationSystem;
use App\Models\StudentApplication;
use Closure;
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
        $gradeId = $this->input('educational_grade_id');
        $systemId = $this->input('education_system_id');
        $registrationType = $this->input('registration_type');
        $grade = is_scalar($gradeId) ? EducationalGrade::with('educationalStage')->availableForRegistration()->find($gradeId) : null;
        $requirements = StudentApplication::registrationRequirements($grade?->educationalStage?->category,
            is_string($registrationType) ? $registrationType : null, $grade?->code);

        return [
            'education_system_id' => ['bail', 'required', 'integer', Rule::exists(EducationSystem::class, 'id')->where('is_active', true)],
            'student_name' => ['bail', 'required', 'string', 'max:150', 'regex:/^[\x{0621}-\x{063A}\x{0641}-\x{065F}\x{0670}-\x{06D3}]+(?: [\x{0621}-\x{063A}\x{0641}-\x{065F}\x{0670}-\x{06D3}]+){2,}$/u'],
            'date_of_birth' => ['required', 'date_format:Y-m-d', 'before:today'],
            'educational_grade_id' => ['bail', 'required', 'integer', function (string $attribute, mixed $value, Closure $fail) use ($grade, $systemId): void {
                if (! $grade || ! is_scalar($systemId) || (string) $grade->educationalStage->education_system_id !== (string) $systemId
                    || ! EducationalGrade::codeMatchesCategory($grade->code, $grade->educationalStage?->category)) {
                    $fail('يرجى اختيار صف متاح ضمن مرحلة مصنفة.');
                }
            }],
            'registration_type' => ['required', 'string', Rule::in(array_keys(StudentApplication::REGISTRATION_TYPES))],
            'guardian_name' => ['required', 'string', 'max:150'],
            'guardian_phone' => ['required', 'string', 'max:20', 'regex:/^(?:[0-9]{8}|\+?[1-9][0-9]{8,14})$/D', 'not_regex:/^0+$/D'],
            'guardian_email' => ['nullable', 'string', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'document_type' => [Rule::excludeIf($requirements['document_required'] !== true), 'required', 'string', Rule::in(array_keys($requirements['document_types']))],
            'foreign_document_attestation_confirmed' => [Rule::excludeIf($requirements['attestation_required'] !== true), 'accepted'],
            'document' => ['bail', Rule::requiredIf($requirements['document_required'] === true), 'nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,image/jpeg,image/png', 'extensions:pdf,jpg,jpeg,png'],
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
            'educational_grade_id.integer' => 'يرجى اختيار صف متاح ضمن مرحلة مصنفة.',
            'education_system_id.exists' => 'يرجى اختيار نظام تعليم متاح.',
            'education_system_id.integer' => 'يرجى اختيار نظام تعليم متاح.',
            'registration_type.in' => 'يرجى اختيار حالة طالب صحيحة.',
            'guardian_phone.regex' => 'يرجى إدخال رقم هاتف محلي من ٨ أرقام أو رقم دولي صحيح.',
            'guardian_phone.not_regex' => 'يرجى إدخال رقم هاتف صحيح.',
            'guardian_email.email' => 'يرجى إدخال بريد إلكتروني صحيح.',
            'document.file' => 'تعذر رفع المستند. يرجى اختيار الملف مجدداً.',
            'document.required' => 'يرجى إرفاق المستند المطلوب وفق حالة الطالب والصف المختار.',
            'document_type.in' => 'نوع المستند لا يطابق متطلبات حالة الطالب والصف المختار.',
            'foreign_document_attestation_confirmed.accepted' => 'يجب أن تكون الإفادة الأجنبية مصدقة من لبنان. يرجى تأكيد ذلك قبل الإرسال.',
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
            'educational_grade_id' => 'الصف المطلوب',
            'education_system_id' => 'نظام التعليم',
            'registration_type' => 'حالة الطالب',
            'guardian_name' => 'اسم ولي الأمر',
            'guardian_phone' => 'هاتف ولي الأمر',
            'guardian_email' => 'البريد الإلكتروني',
            'notes' => 'الملاحظات',
            'document' => 'المستند',
            'document_type' => 'نوع المستند',
            'foreign_document_attestation_confirmed' => 'تأكيد تصديق الإفادة من لبنان',
        ];
    }
}
