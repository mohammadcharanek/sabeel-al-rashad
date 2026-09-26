<?php

namespace App\Models;

use Database\Factories\StudentApplicationFactory;
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

    protected $fillable = [
        'student_name', 'date_of_birth', 'educational_stage_id',
        'guardian_name', 'guardian_phone', 'guardian_email', 'notes',
        'status', 'admin_note',
    ];

    protected $attributes = ['status' => 'pending'];

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
