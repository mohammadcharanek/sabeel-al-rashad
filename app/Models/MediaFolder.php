<?php

namespace App\Models;

use Database\Factories\MediaFolderFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MediaFolder extends Model
{
    /** @use HasFactory<MediaFolderFactory> */
    use HasFactory;

    protected $fillable = ['title', 'slug', 'description', 'cover', 'media_type', 'sort_order', 'is_published'];

    protected $attributes = ['is_published' => false, 'sort_order' => 0];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        static::updating(function (MediaFolder $folder): void {
            if ($folder->isDirty('media_type') && $folder->hasMedia()) {
                throw ValidationException::withMessages(['media_type' => 'أعد تعيين محتويات المجلد قبل تغيير نوعه.']);
            }
        });

        static::deleting(function (MediaFolder $folder): void {
            if ($folder->hasMedia()) {
                throw ValidationException::withMessages(['folder' => 'أعد تعيين محتويات المجلد قبل حذفه.']);
            }
        });
    }

    public function photos(): HasMany
    {
        return $this->hasMany(GalleryItem::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    public function hasMedia(): bool
    {
        return $this->photos()->exists() || $this->videos()->exists();
    }

    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function coverUrl(): ?string
    {
        $path = $this->cover;

        if (! is_string($path) || ! preg_match('~\Amedia-covers/(?:[A-Za-z0-9_-]+/)*[A-Za-z0-9_-]+\.(?:jpg|jpeg|png|webp)\z~i', $path)) {
            return null;
        }

        return Storage::disk('public')->exists($path) ? Storage::disk('public')->url($path) : null;
    }
}
