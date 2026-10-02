<?php

namespace App\Models;

use Database\Factories\StudentApplicationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentApplication extends Model
{
    /** @use HasFactory<StudentApplicationFactory> */
    use HasFactory;

    public const STATUSES = [
        'pending' => 'قيد الانتظار',
        'under_review' => 'قيد المراجعة',
        'accepted' => 'مقبول',
        'rejected' => 'مرفوض',
    ];

    public const REGISTRATION_TYPES = [
        'current_student' => 'طالب حالي',
        'new_student' => 'طالب جديد',
        'transferred_student' => 'طالب منتقل من مدرسة أخرى',
        'traveler' => 'طالب مسافر',
    ];

    protected $fillable = [
        'student_name', 'date_of_birth', 'educational_stage_id', 'educational_grade_id', 'registration_type',
        'guardian_name', 'guardian_phone', 'guardian_email', 'notes',
        'status', 'admin_note',
    ];

    protected $attributes = ['status' => 'pending', 'registration_type' => 'current_student'];

    /** @return array{available: bool, document_required: ?bool, interview_required: ?bool, entrance_exam_required: ?bool, document_summary: string, interview_summary: string, exam_summary: string} */
    public static function registrationRequirements(?string $category, ?string $registrationType, ?string $gradeCode = null): array
    {
        $unknown = [
            'available' => false,
            'document_required' => null,
            'interview_required' => null,
            'entrance_exam_required' => null,
            'document_summary' => 'يرجى اختيار حالة الطالب والصف المطلوب لمعرفة المتطلبات.',
            'interview_summary' => '',
            'exam_summary' => '',
        ];

        if (! array_key_exists($registrationType ?? '', self::REGISTRATION_TYPES)
            || ! array_key_exists($category ?? '', EducationalStage::CATEGORIES)
            || ($gradeCode !== null && ! EducationalGrade::codeMatchesCategory($gradeCode, $category))) {
            return $unknown;
        }

        if ($registrationType === 'new_student' && ($category !== 'kindergarten' || $gradeCode === null)) {
            return array_replace($unknown, ['document_summary' => 'تسجيل الطالب الجديد متاح حالياً لصفوف الروضات فقط. يرجى التواصل مع إدارة المدرسة.']);
        }

        $kindergartenAdmission = $category === 'kindergarten' && $gradeCode !== null && $registrationType !== 'current_student';
        $documentRequired = ! ($kindergartenAdmission && ($registrationType === 'new_student'
            || ($registrationType === 'traveler' && $gradeCode === 'kg1')));
        $examRequired = ! $kindergartenAdmission && match ($registrationType) {
            'current_student', 'new_student' => false,
            'transferred_student' => in_array($category, EducationalStage::EXAM_CATEGORIES, true),
            'traveler' => true,
        };

        return [
            'available' => true,
            'document_required' => $documentRequired,
            'interview_required' => $kindergartenAdmission,
            'entrance_exam_required' => $examRequired,
            'document_summary' => ! $documentRequired
                ? ($registrationType === 'traveler' ? 'لا يلزم إرفاق إفادة لهذه الحالة.' : 'لا يلزم إرفاق إفادة.')
                : match ($registrationType) {
                    'current_student' => 'المستند المطلوب: إفادة أو شهادة نجاح من الصف السابق.',
                    'transferred_student' => $category === 'kindergarten'
                        ? 'المستند المطلوب: إفادة من المدرسة أو الروضة السابقة.'
                        : 'المستند المطلوب: إفادة أو شهادة نجاح من المدرسة السابقة.',
                    default => 'المستند المطلوب: إفادة.',
                },
            'interview_summary' => $kindergartenAdmission ? 'يلزم إجراء مقابلة.' : 'لا توجد مقابلة مطلوبة.',
            'exam_summary' => $examRequired ? 'يخضع الطالب لامتحان دخول.' : ($registrationType === 'current_student'
                ? 'لا يخضع الطالب الحالي لامتحان دخول.' : 'لا يوجد امتحان دخول لهذه المرحلة.'),
        ];
    }

    /** @return array{available: bool, document_required: ?bool, interview_required: ?bool, entrance_exam_required: ?bool, document_summary: string, interview_summary: string, exam_summary: string} */
    public function requirements(): array
    {
        return self::registrationRequirements($this->educationalStage?->category, $this->registration_type, $this->educationalGrade?->code);
    }

    public static function filterByRequirement(Builder $query, string $requirement, bool $required): Builder
    {
        return $query->where(function (Builder $matches) use ($requirement, $required): void {
            $matches->whereRaw('1 = 0');
            foreach (array_keys(EducationalStage::CATEGORIES) as $category) {
                $codes = $category === 'kindergarten' ? [null, ...EducationalGrade::KINDERGARTEN_CODES] : [null, 'grade_1'];
                foreach (array_keys(self::REGISTRATION_TYPES) as $type) {
                    foreach ($codes as $code) {
                        if (self::registrationRequirements($category, $type, $code)[$requirement] !== $required) {
                            continue;
                        }
                        $matches->orWhere(function (Builder $match) use ($category, $type, $code): void {
                            $match->where('registration_type', $type)
                                ->whereHas('educationalStage', fn (Builder $stage): Builder => $stage->where('category', $category));
                            if ($code === null) {
                                $match->whereNull('educational_grade_id');
                            } else {
                                $match->whereHas('educationalGrade', function (Builder $grade) use ($category, $code): void {
                                    $grade->whereColumn('educational_grades.educational_stage_id', 'student_applications.educational_stage_id');
                                    if ($category === 'kindergarten') {
                                        $grade->where('code', $code);
                                    } else {
                                        $grade->where('code', 'like', 'grade_%');
                                    }
                                });
                            }
                        });
                    }
                }
            }
        });
    }

    public static function filterByEntranceExamRequirement(Builder $query, bool $required): Builder
    {
        return self::filterByRequirement($query, 'entrance_exam_required', $required);
    }

    protected function documentRequired(): Attribute
    {
        return Attribute::make(get: fn (): ?bool => $this->requirements()['document_required']);
    }

    protected function interviewRequired(): Attribute
    {
        return Attribute::make(get: fn (): ?bool => $this->requirements()['interview_required']);
    }

    protected function entranceExamRequired(): Attribute
    {
        return Attribute::make(get: fn (): ?bool => $this->requirements()['entrance_exam_required']);
    }

    public function educationalGrade(): BelongsTo
    {
        return $this->belongsTo(EducationalGrade::class);
    }

    protected static function booted(): void
    {
        static::creating(function (StudentApplication $application): void {
            $application->reference_number = 'SAR-'.now()->format('Y').'-'.implode('-', str_split(strtoupper(bin2hex(random_bytes(10))), 5));
            $application->submitted_at = now();
        });
    }

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'submitted_at' => 'datetime',
        ];
    }

    public function educationalStage(): BelongsTo
    {
        return $this->belongsTo(EducationalStage::class);
    }
}
