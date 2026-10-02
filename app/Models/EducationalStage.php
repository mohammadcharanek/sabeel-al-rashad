<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class EducationalStage extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'kindergarten' => 'روضات',
        'primary' => 'ابتدائي',
        'intermediate' => 'متوسط',
        'secondary' => 'ثانوي',
    ];

    public const EXAM_CATEGORIES = ['primary', 'intermediate', 'secondary'];

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

    protected static function booted(): void
    {
        static::updating(function (EducationalStage $stage): void {
            if ($stage->isDirty('category') && ($stage->educationalGrades()->exists()
                || StudentApplication::where('educational_stage_id', $stage->id)->exists())) {
                throw ValidationException::withMessages(['category' => 'لا يمكن تغيير تصنيف مرحلة مرتبطة بصفوف أو طلبات تسجيل.']);
            }
        });
    }
}
