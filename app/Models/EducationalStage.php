<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class EducationalStage extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'kindergarten' => 'روضات',
        'basic' => 'التعليم الأساسي',
        'primary' => 'ابتدائي',
        'intermediate' => 'متوسط',
        'secondary' => 'ثانوي',
        'elementary' => 'المرحلة الابتدائية',
        'middle_school' => 'المرحلة المتوسطة',
        'high_school' => 'المرحلة الثانوية',
    ];

    /** @var array<string, array{int, int}> */
    public const AMERICAN_GRADE_RANGES = [
        'elementary' => [1, 5],
        'middle_school' => [6, 8],
        'high_school' => [9, 12],
    ];

    public const EXAM_CATEGORIES = ['basic', 'primary', 'intermediate', 'secondary'];

    protected $fillable = [
        'education_system_id',
        'title',
        'category',
        'slug',
        'description',
        'icon',
        'image',
        'link_label',
        'link_url',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function educationalGrades(): HasMany
    {
        return $this->hasMany(EducationalGrade::class);
    }

    public function educationSystem(): BelongsTo
    {
        return $this->belongsTo(EducationSystem::class);
    }

    /** @return array<string, string> */
    public static function iconOptions(): array
    {
        return array_diff_key(self::CATEGORIES, ['elementary' => true])
            + ['american_elementary' => self::CATEGORIES['elementary']];
    }

    public function scopeClassified(Builder $query): Builder
    {
        return $query->where(function (Builder $stages): void {
            $stages->where(function (Builder $american): void {
                $american->whereIn('category', array_keys(self::AMERICAN_GRADE_RANGES))
                    ->whereHas('educationSystem', fn (Builder $system): Builder => $system->where('slug', 'american'));
            })->orWhere(function (Builder $national): void {
                $national->whereIn('category', array_keys(array_diff_key(self::CATEGORIES, self::AMERICAN_GRADE_RANGES)))
                    ->whereHas('educationSystem', fn (Builder $system): Builder => $system->where('slug', '!=', 'american'));
            });
        });
    }

    public function scopeAvailableForRegistration(Builder $query): Builder
    {
        return $query->classified()->where('is_active', true)
            ->whereHas('educationSystem', fn (Builder $system): Builder => $system->where('is_active', true));
    }

    public function studentApplications(): HasMany
    {
        return $this->hasMany(StudentApplication::class);
    }

    protected function icon(): Attribute
    {
        $normalize = fn (?string $value): ?string => match ($value) {
            'Kindergarten Stage' => 'kindergarten',
            'Basic Education Stage' => 'basic',
            'elementary' => 'primary',
            'middle' => 'intermediate',
            default => $value,
        };

        return Attribute::make(get: $normalize, set: $normalize);
    }

    public function deletionBlockReason(): ?string
    {
        if ($this->educationalGrades()->exists()) {
            return 'لا يمكن حذف المرحلة لأنها تحتوي على صفوف مرتبطة بها. يرجى حذف الصفوف أو نقلها إلى مرحلة أخرى أولاً.';
        }

        if ($this->studentApplications()->exists()) {
            return 'لا يمكن حذف المرحلة لارتباطها بطلبات تسجيل. يمكن إخفاؤها بدلاً من حذفها للحفاظ على بيانات التسجيل.';
        }

        return null;
    }

    protected static function booted(): void
    {
        static::saving(function (EducationalStage $stage): void {
            if ($stage->isDirty('category') && $stage->category !== null
                && ! array_key_exists($stage->category, self::CATEGORIES)) {
                throw ValidationException::withMessages(['category' => 'يرجى اختيار تصنيف مرحلة صحيح.']);
            }

            if ($stage->isDirty(['education_system_id', 'category']) && $stage->education_system_id !== null) {
                $system = $stage->educationSystem()->first();
                if (! $system || ($stage->category !== null && ! array_key_exists($stage->category, $system->stageCategories()))) {
                    throw ValidationException::withMessages(['category' => 'تصنيف المرحلة لا يتوافق مع نظام التعليم.']);
                }
            }
        });

        static::deleting(fn (EducationalStage $stage): bool => $stage->deletionBlockReason() === null);

        static::updating(function (EducationalStage $stage): void {
            if ($stage->isDirty(['category', 'education_system_id']) && ($stage->educationalGrades()->exists()
                || $stage->studentApplications()->exists())) {
                throw ValidationException::withMessages(['category' => 'لا يمكن تغيير نظام أو تصنيف مرحلة مرتبطة بصفوف أو طلبات تسجيل.']);
            }
        });
    }
}
