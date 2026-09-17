<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GalleryItem extends Model
{
    protected $fillable = [
        'title', 'image', 'alt_text', 'caption', 'category',
        'sort_order', 'is_active', 'is_featured',
    ];

    protected $attributes = ['is_active' => false, 'is_featured' => false];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_featured' => 'boolean', 'sort_order' => 'integer'];
    }

    public function imageUrl(): ?string
    {
        $path = $this->image;

        if (! is_string($path) || ! preg_match('~\Agallery/[A-Za-z0-9/_-]+\.(?:jpg|jpeg|png|webp)\z~i', $path)) {
            return null;
        }

        if (str_contains($path, '//') || str_contains($path, '/./') || str_contains($path, '/../')) {
            return null;
        }

        return Storage::disk('public')->exists($path) ? Storage::disk('public')->url($path) : null;
    }
}
