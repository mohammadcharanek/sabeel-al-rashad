<?php

namespace App\Filament\Resources\Features\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeatureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('معلومات الميزة')
                ->description('أدخل محتوى معتمداً من المدرسة. العناصر الجديدة غير منشورة افتراضياً.')
                ->schema([
                    TextInput::make('title')
                        ->label('العنوان')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label('الوصف')
                        ->rows(4)
                        ->columnSpanFull(),
                    Select::make('icon')
                        ->label('الأيقونة')
                        ->options([
                            'academic' => 'التعليم الأكاديمي',
                            'safe' => 'بيئة آمنة',
                            'staff' => 'الكادر التعليمي',
                            'values' => 'القيم والأخلاق',
                            'activities' => 'الأنشطة',
                            'followup' => 'المتابعة',
                        ])
                        ->default('academic')
                        ->required(),
                    TextInput::make('sort_order')
                        ->label('الترتيب')
                        ->integer()
                        ->minValue(0)
                        ->maxValue(4294967295)
                        ->default(0)
                        ->required(),
                    Toggle::make('is_active')
                        ->label('نشر الميزة على الموقع')
                        ->default(false),
                ])
                ->columns(2),
        ]);
    }
}
