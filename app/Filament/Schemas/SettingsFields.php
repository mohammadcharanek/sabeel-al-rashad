<?php

namespace App\Filament\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Validation\Rule;

class SettingsFields
{
    public static function text(string $name, string $label, int $length = 255): TextInput
    {
        return TextInput::make($name)->label($label)->maxLength($length);
    }

    public static function paragraph(string $name, string $label): Textarea
    {
        return Textarea::make($name)->label($label)->rows(3)->maxLength(5000);
    }

    public static function url(string $name, string $label, int $length = 2048): TextInput
    {
        return self::text($name, $label, $length)->url()->rules(['url:http,https']);
    }

    public static function image(string $name, string $label, string $directory): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->rules(['mimes:jpg,jpeg,png,webp', Rule::dimensions()->maxWidth(6000)->maxHeight(6000)])
            ->disk('public')
            ->directory($directory)
            ->visibility('public')
            ->maxSize(5120)
            ->preventFilePathTampering();
    }

    public static function normalizePhone(?string $state): ?string
    {
        if (blank($state)) {
            return null;
        }

        $number = preg_replace('/[\\s().-]+/u', '', $state);

        if (str_starts_with($number, '00')) {
            return '+'.substr($number, 2);
        }

        return str_starts_with($number, '+') ? $number : '+'.$number;
    }
}
