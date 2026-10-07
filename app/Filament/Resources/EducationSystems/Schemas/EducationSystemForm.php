<?php

namespace App\Filament\Resources\EducationSystems\Schemas;

use App\Models\EducationSystem;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EducationSystemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('اسم نظام التعليم')->required()->maxLength(255),
            TextInput::make('slug')->label('الرمز الثابت')->required()->maxLength(255)
                ->regex('/\A[a-z][a-z0-9-]*\z/')->unique(ignoreRecord: true)
                ->disabled(fn (?EducationSystem $record): bool => $record?->educationalStages()->exists() ?? false)
                ->helperText('الرمز american مخصص للمنهج الأميركي. لا يمكن تغيير الرمز بعد ربط المراحل.'),
            Textarea::make('description')->label('الوصف')->maxLength(3000)->columnSpanFull(),
            TextInput::make('sort_order')->label('الترتيب')->integer()->minValue(0)->maxValue(4294967295)->default(0)->required(),
            Toggle::make('is_active')->label('ظاهر في الموقع والتسجيل')->default(true),
        ]);
    }
}
