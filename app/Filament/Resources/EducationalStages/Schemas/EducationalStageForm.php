<?php

namespace App\Filament\Resources\EducationalStages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EducationalStageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('معلومات المرحلة')
                    ->description('أدخل معلومات المرحلة التعليمية كما ستظهر في الموقع.')
                    ->schema([

                        TextInput::make('title')
                            ->label('اسم المرحلة')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn (?string $state, callable $set) => $set('slug', Str::slug($state ?? ''))
                            ),

                        TextInput::make('slug')
                            ->label('الرابط المختصر')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('سيُستخدم لاحقاً في رابط صفحة المرحلة.'),

                        Textarea::make('description')
                            ->label('الوصف')
                            ->rows(4)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

                Section::make('المظهر')
                    ->schema([

                        Select::make('icon')
                            ->label('الأيقونة')
                            ->options([
                                'elementary' => 'المرحلة الابتدائية',
                                'middle' => 'المرحلة المتوسطة',
                                'secondary' => 'المرحلة الثانوية',
                            ])
                            ->default('elementary')
                            ->required(),

                        FileUpload::make('image')
                            ->label('صورة المرحلة')
                            ->image()
                            ->disk('public')
                            ->directory('educational-stages')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(5120),

                    ])
                    ->columns(2),

                Section::make('الرابط')
                    ->schema([

                        TextInput::make('link_label')
                            ->label('نص الرابط')
                            ->maxLength(100)
                            ->placeholder('تعرف أكثر'),

                        TextInput::make('link_url')
                            ->label('رابط المرحلة')
                            ->maxLength(255)
                            ->url()
                            ->rules(['url:http,https'])
                            ->placeholder('https://example.com/...'),

                    ])
                    ->columns(2),

                Section::make('النشر والترتيب')
                    ->schema([

                        TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(4294967295),

                        Toggle::make('is_active')
                            ->label('إظهار المرحلة في الموقع')
                            ->default(true),

                    ])
                    ->columns(2),

            ]);
    }
}
