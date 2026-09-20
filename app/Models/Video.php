<?php

namespace App\Models;

use Database\Factories\VideoFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Video extends Model
{
    /** @use HasFactory<VideoFactory> */
    use HasFactory;

    protected $fillable = ['title', 'description', 'video', 'poster', 'media_folder_id', 'sort_order', 'is_published'];

    protected $attributes = ['is_published' => false, 'sort_order' => 0];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'sort_order' => 'integer'];
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'media_folder_id');
    }

    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_published', true)->where(function (Builder $query): void {
            $query->whereNull('media_folder_id')
                ->orWhereHas('folder', fn (Builder $folder): Builder => $folder->published()->where('media_type', 'video'));
        });
    }

    public function videoUrl(): ?string
    {
        return $this->mediaUrl($this->video, 'videos', 'mp4|webm');
    }

    public function posterUrl(): ?string
    {
        return $this->mediaUrl($this->poster, 'video-posters', 'jpg|jpeg|png|webp');
    }

    public function mimeType(): string
    {
        return strtolower(pathinfo($this->video, PATHINFO_EXTENSION)) === 'webm' ? 'video/webm' : 'video/mp4';
    }

    private function mediaUrl(?string $path, string $directory, string $extensions): ?string
    {
        if ($path === null || ! preg_match('~\A'.$directory.'/(?:[A-Za-z0-9_-]+/)*[A-Za-z0-9_-]+\.(?:'.$extensions.')\z~i', $path)) {
            return null;
        }

        return Storage::disk('public')->exists($path) ? Storage::disk('public')->url($path) : null;
    }
}
