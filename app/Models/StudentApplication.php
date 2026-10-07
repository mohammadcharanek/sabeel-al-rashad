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

    public const DOCUMENT_TYPES = [
        'school_statement' => 'إفادة من المدرسة السابقة',
        'school_certificate' => 'شهادة من المدرسة السابقة',
        'statement_and_certificate' => 'إفادة وشهادة معاً في ملف واحد',
        'foreign_statement' => 'إفادة من البلد القادم منه الطالب مصدقة من لبنان',
    ];

    protected $fillable = [
        'student_name', 'date_of_birth', 'educational_stage_id', 'educational_grade_id', 'registration_type',
        'document_type', 'foreign_document_attestation_confirmed',
        'guardian_name', 'guardian_phone', 'guardian_email', 'notes',
        'status', 'admin_note',
    ];

    protected $attributes = ['status' => 'pending', 'registration_type' => 'current_student'];

    /** @return array{available: bool, document_required: ?bool, document_types: array<string, string>, attestation_required: ?bool, interview_required: ?bool, entrance_exam_required: ?bool, document_summary: string, interview_summary: string, exam_summary: string} */
    public static function registrationRequirements(?string $category, ?string $registrationType, ?string $gradeCode = null): array
    {
        $unknown = [
            'available' => false,
            'document_required' => null,
            'document_types' => [],
            'attestation_required' => null,
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

        $interviewRequired = $registrationType === 'new_student';
        $examRequired = $category !== 'kindergarten' && in_array($registrationType, ['transferred_student', 'traveler'], true);
        $documentRequired = $registrationType !== 'new_student';
        $gradeTenTransfer = $registrationType === 'transferred_student' && in_array($gradeCode, ['grade_10', 'american_grade_10'], true);
        $documentTypes = match ($registrationType) {
            'traveler' => ['foreign_statement'],
            'transferred_student' => $gradeTenTransfer ? ['school_statement', 'school_certificate', 'statement_and_certificate'] : ['school_statement'],
            'current_student' => ['school_statement', 'school_certificate', 'statement_and_certificate'],
            default => [],
        };

        return [
            'available' => true,
            'document_required' => $documentRequired,
            'document_types' => array_intersect_key(self::DOCUMENT_TYPES, array_flip($documentTypes)),
            'attestation_required' => $registrationType === 'traveler',
            'interview_required' => $interviewRequired,
            'entrance_exam_required' => $examRequired,
            'document_summary' => ! $documentRequired
                ? 'لا يلزم إرفاق إفادة.'
                : match ($registrationType) {
                    'current_student' => 'المستند المطلوب: إفادة أو شهادة نجاح من الصف السابق.',
                    'transferred_student' => $gradeTenTransfer
                        ? 'المستند المطلوب: إفادة أو شهادة من المدرسة السابقة. يكفي أحدهما، ويمكن إرفاقهما معاً في ملف واحد.'
                        : 'المستند المطلوب: إفادة من المدرسة السابقة.',
                    default => 'المستند المطلوب: إفادة من البلد القادم منه الطالب مصدقة من لبنان.',
                },
            'interview_summary' => $interviewRequired ? 'يلزم إجراء مقابلة.' : 'لا توجد مقابلة مطلوبة.',
            'exam_summary' => $examRequired ? 'يخضع الطالب لامتحان دخول.' : ($registrationType === 'current_student'
                ? 'لا يخضع الطالب الحالي لامتحان دخول.' : 'لا يوجد امتحان دخول لهذه المرحلة.'),
        ];
    }

    /** @return array{available: bool, document_required: ?bool, document_types: array<string, string>, attestation_required: ?bool, interview_required: ?bool, entrance_exam_required: ?bool, document_summary: string, interview_summary: string, exam_summary: string} */
    public function requirements(): array
    {
        $grade = $this->educationalGrade;

        return self::registrationRequirements($grade?->educationalStage?->category ?? $this->educationalStage?->category, $this->registration_type, $grade?->code);
    }

    public static function filterByRequirement(Builder $query, string $requirement, bool $required): Builder
    {
        return $query->where(function (Builder $matches) use ($requirement, $required): void {
            $matches->whereRaw('1 = 0');
            foreach (array_keys(EducationalStage::CATEGORIES) as $category) {
                $americanRange = EducationalStage::AMERICAN_GRADE_RANGES[$category] ?? null;
                $codes = $americanRange
                    ? [null, ...array_map(fn (int $number): string => 'american_grade_'.$number, range(...$americanRange))]
                    : ($category === 'kindergarten' ? [null, ...EducationalGrade::KINDERGARTEN_CODES] : [null, 'grade_1']);
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
                                    if ($category === 'kindergarten' || isset(EducationalStage::AMERICAN_GRADE_RANGES[$category])) {
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
            'foreign_document_attestation_confirmed' => 'boolean',
        ];
    }

    public function educationalStage(): BelongsTo
    {
        return $this->belongsTo(EducationalStage::class);
    }
}
