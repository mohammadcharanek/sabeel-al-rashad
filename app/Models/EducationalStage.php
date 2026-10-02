<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
