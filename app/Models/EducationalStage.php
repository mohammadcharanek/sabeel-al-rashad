<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    ];

    public const EXAM_CATEGORIES = ['basic', 'primary', 'intermediate', 'secondary'];

    protected $fillable = [
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
        });

        static::deleting(fn (EducationalStage $stage): bool => $stage->deletionBlockReason() === null);

        static::updating(function (EducationalStage $stage): void {
            if ($stage->isDirty('category') && ($stage->educationalGrades()->exists()
                || $stage->studentApplications()->exists())) {
                throw ValidationException::withMessages(['category' => 'لا يمكن تغيير تصنيف مرحلة مرتبطة بصفوف أو طلبات تسجيل.']);
            }
        });
    }
}
