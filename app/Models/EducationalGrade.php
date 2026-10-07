<?php

namespace App\Models;

use Database\Factories\EducationalGradeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class EducationalGrade extends Model
{
    /** @use HasFactory<EducationalGradeFactory> */
    use HasFactory;

    public const KINDERGARTEN_CODES = ['kg1', 'kg2', 'kg3'];

    protected $fillable = ['educational_stage_id', 'name_ar', 'code', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public static function codeMatchesCategory(?string $code, ?string $category): bool
    {
        if (isset(EducationalStage::AMERICAN_GRADE_RANGES[$category])) {
            [$first, $last] = EducationalStage::AMERICAN_GRADE_RANGES[$category];

            return in_array($code, array_map(fn (int $number): string => 'american_grade_'.$number, range($first, $last)), true);
        }

        return $category === 'kindergarten'
            ? in_array($code, self::KINDERGARTEN_CODES, true)
            : in_array($category, EducationalStage::EXAM_CATEGORIES, true)
                && preg_match('/\Agrade_[1-9][0-9]*\z/', $code ?? '') === 1;
    }

    public function scopeAvailableForRegistration(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereHas('educationalStage', fn (Builder $stage): Builder => $stage->availableForRegistration());
    }

    protected static function booted(): void
    {
        static::saving(function (EducationalGrade $grade): void {
            if ($grade->exists && $grade->isDirty(['name_ar', 'code', 'educational_stage_id']) && $grade->studentApplications()->exists()) {
                throw ValidationException::withMessages(['code' => 'لا يمكن تغيير هوية صف مرتبط بطلبات تسجيل. يمكن تعديل ترتيبه أو تفعيله فقط.']);
            }

            $stage = $grade->educationalStage()->classified()->first();
            if (! $stage || ! self::codeMatchesCategory($grade->code, $stage->category)) {
                throw ValidationException::withMessages(['code' => 'رمز الصف لا يتوافق مع المرحلة ونظام التعليم.']);
            }
        });
    }

    public function educationalStage(): BelongsTo
    {
        return $this->belongsTo(EducationalStage::class);
    }

    public function studentApplications(): HasMany
    {
        return $this->hasMany(StudentApplication::class);
    }
}
