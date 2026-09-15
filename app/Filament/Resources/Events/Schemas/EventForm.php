<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('معلومات الفعالية')
                    ->description('أدخل المعلومات الأساسية للفعالية المدرسية.')
                    ->schema([

                        TextInput::make('title')
                            ->label('عنوان الفعالية')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn (?string $state, callable $set) =>
                                    $set('slug', Str::slug($state ?? ''))
                            ),

                        TextInput::make('slug')
                            ->label('الرابط المختصر')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('سيُستخدم لاحقاً في رابط صفحة الفعالية.'),

                        Textarea::make('description')
                            ->label('وصف الفعالية')
                            ->rows(4)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

                Section::make('التاريخ والمكان')
                    ->schema([

                        DateTimePicker::make('starts_at')
                            ->label('تاريخ ووقت البداية')
                            ->required()
                            ->seconds(false),

                        DateTimePicker::make('ends_at')
                            ->label('تاريخ ووقت النهاية')
                            ->seconds(false)
                            ->afterOrEqual('starts_at'),

                        TextInput::make('location')
                            ->label('المكان')
                            ->maxLength(255)
                            ->placeholder('مثال: قاعة المدرسة'),

                    ])
                    ->columns(3),

                Section::make('الصورة')
                    ->schema([

                        FileUpload::make('image')
                            ->label('صورة الفعالية')
                            ->image()
                            ->disk('public')
                            ->directory('events')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(5120)
                            ->columnSpanFull(),

                    ]),

                Section::make('النشر والترتيب')
                    ->schema([

                        Toggle::make('is_published')
                            ->label('نشر الفعالية')
                            ->default(false),

                        TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(4294967295),

                    ])
                    ->columns(2),

            ]);
    }
}