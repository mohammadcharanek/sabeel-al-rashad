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
        'transferred_student' => 'طالب منتقل من مدرسة أخرى',
        'traveler' => 'طالب مسافر',
    ];

    protected $fillable = [
        'student_name', 'date_of_birth', 'educational_stage_id', 'registration_type',
        'guardian_name', 'guardian_phone', 'guardian_email', 'notes',
        'status', 'admin_note',
    ];

    protected $attributes = ['status' => 'pending', 'registration_type' => 'current_student'];

    /** @return array{document_required: ?bool, entrance_exam_required: ?bool, document_summary: string, exam_summary: string} */
    public static function registrationRequirements(?string $category, ?string $registrationType): array
    {
        if (! array_key_exists($registrationType ?? '', self::REGISTRATION_TYPES)
            || ! array_key_exists($category ?? '', EducationalStage::CATEGORIES)) {
            return [
                'document_required' => null,
                'entrance_exam_required' => null,
                'document_summary' => 'المستند مطلوب. يرجى اختيار حالة الطالب والمرحلة التعليمية لمعرفة التفاصيل.',
                'exam_summary' => 'يُحدّد امتحان الدخول بحسب حالة الطالب وتصنيف المرحلة.',
            ];
        }

        $examRequired = match ($registrationType) {
            'current_student' => false,
            'transferred_student' => in_array($category, EducationalStage::EXAM_CATEGORIES, true),
            'traveler' => true,
        };

        return [
            'document_required' => true,
            'entrance_exam_required' => $examRequired,
            'document_summary' => match ($registrationType) {
                'current_student' => 'المستند المطلوب: إفادة أو شهادة نجاح من الصف السابق.',
                'transferred_student' => $category === 'kindergarten'
                    ? 'المستند المطلوب: إفادة من المدرسة أو الروضة السابقة.'
                    : 'المستند المطلوب: إفادة أو شهادة نجاح من المدرسة السابقة.',
                'traveler' => 'المستند المطلوب: إفادة.',
            },
            'exam_summary' => $examRequired ? 'يخضع الطالب لامتحان دخول.' : ($registrationType === 'current_student'
                ? 'لا يخضع الطالب الحالي لامتحان دخول.'
                : 'لا يوجد امتحان دخول لهذه المرحلة.'),
        ];
    }

    public static function filterByEntranceExamRequirement(Builder $query, bool $required): Builder
    {
        return $query->where(function (Builder $requirements) use ($required): void {
            foreach (array_keys(self::REGISTRATION_TYPES) as $type) {
                $categories = array_filter(array_keys(EducationalStage::CATEGORIES),
                    fn (string $category): bool => self::registrationRequirements($category, $type)['entrance_exam_required'] === $required);

                if ($categories !== []) {
                    $requirements->orWhere(fn (Builder $match): Builder => $match
                        ->where('registration_type', $type)
                        ->whereHas('educationalStage', fn (Builder $stage): Builder => $stage->whereIn('category', $categories)));
                }
            }
        });
    }

    protected function documentRequired(): Attribute
    {
        return Attribute::make(
            get: fn (): ?bool => self::registrationRequirements($this->educationalStage?->category, $this->registration_type)['document_required'],
        );
    }

    protected function entranceExamRequired(): Attribute
    {
        return Attribute::make(
            get: fn (): ?bool => self::registrationRequirements($this->educationalStage?->category, $this->registration_type)['entrance_exam_required'],
        );
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
