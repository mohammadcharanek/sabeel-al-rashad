<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

abstract class SingletonSetting extends Model
{
    protected static function booted(): void
    {
        static::saving(function (SingletonSetting $settings): void {
            $settings->singleton_key = 'global';
        });
    }

    public static function current(): static
    {
        return static::query()->where('singleton_key', 'global')->first() ?? new static;
    }

    public function text(string $attribute, string $fallback = ''): string
    {
        $value = $this->getAttribute($attribute);

        return is_string($value) && trim($value) !== '' ? $value : $fallback;
    }

    public function linkUrl(string $attribute, ?string $fallback = null): ?string
    {
        $url = trim($this->text($attribute));

        return filter_var($url, FILTER_VALIDATE_URL)
            && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)
                ? $url
                : $fallback;
    }

    public function imageUrl(string $attribute, ?string $fallback = null): ?string
    {
        $path = $this->text($attribute);

        if ($path !== ''
            && ! str_starts_with($path, '/')
            && ! str_contains($path, '\\')
            && ! preg_match('/[:\x00-\x1F%?#]/', $path)
            && ! in_array('..', explode('/', $path), true)
            && in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true)
            && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return $fallback !== null && is_file(public_path($fallback)) ? asset($fallback) : null;
    }
}
