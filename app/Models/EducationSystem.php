<?php

namespace App\Models;

use Database\Factories\EducationSystemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class EducationSystem extends Model
{
    /** @use HasFactory<EducationSystemFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function educationalStages(): HasMany
    {
        return $this->hasMany(EducationalStage::class);
    }

    /** @return array<string, string> */
    public function stageCategories(): array
    {
        return $this->slug === 'american'
            ? array_intersect_key(EducationalStage::CATEGORIES, EducationalStage::AMERICAN_GRADE_RANGES)
            : array_diff_key(EducationalStage::CATEGORIES, EducationalStage::AMERICAN_GRADE_RANGES);
    }

    public function deletionBlockReason(): ?string
    {
        return $this->educationalStages()->exists()
            ? 'لا يمكن حذف نظام التعليم لأنه يحتوي على مراحل تعليمية مرتبطة به.'
            : null;
    }

    protected static function booted(): void
    {
        static::updating(function (EducationSystem $system): void {
            if ($system->isDirty('slug') && $system->educationalStages()->exists()) {
                throw ValidationException::withMessages(['slug' => 'لا يمكن تغيير رمز نظام تعليم مرتبط بمراحل تعليمية.']);
            }
        });

        static::deleting(fn (EducationSystem $system): bool => $system->deletionBlockReason() === null);
    }
}
