<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GalleryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'image', 'alt_text', 'caption', 'category',
        'sort_order', 'is_active', 'is_featured', 'media_folder_id',
    ];

    protected $attributes = ['is_active' => false, 'is_featured' => false];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_featured' => 'boolean', 'sort_order' => 'integer'];
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'media_folder_id');
    }

    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_active', true)->where(function (Builder $query): void {
            $query->whereNull('media_folder_id')
                ->orWhereHas('folder', fn (Builder $folder): Builder => $folder->published()->where('media_type', 'photo'));
        });
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
