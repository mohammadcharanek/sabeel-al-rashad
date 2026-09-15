<?php

namespace App\Filament\Resources\NewsPosts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class NewsPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('معلومات الخبر')
                    ->schema([

                        TextInput::make('title')
                            ->label('عنوان الخبر')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ?string $old, string $operation): void {
                                if ($operation === 'create' && (blank($get('slug')) || $get('slug') === Str::slug($old ?? ''))) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('الرابط المختصر')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Select::make('category')
                            ->label('التصنيف')
                            ->options([
                                'أخبار المدرسة' => 'أخبار المدرسة',
                                'الأنشطة الرياضية' => 'الأنشطة الرياضية',
                                'الإنجازات الأكاديمية' => 'الإنجازات الأكاديمية',
                                'الفعاليات المدرسية' => 'الفعاليات المدرسية',
                                'إعلانات' => 'إعلانات',
                            ])
                            ->searchable()
                            ->native(false),

                        Textarea::make('excerpt')
                            ->label('ملخص الخبر')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

                Section::make('المحتوى')
                    ->schema([

                        FileUpload::make('featured_image')
                            ->label('الصورة الرئيسية')
                            ->image()
                            ->disk('public')
                            ->directory('news')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(5120),

                        RichEditor::make('body')
                            ->label('محتوى الخبر')
                            ->columnSpanFull(),

                    ]),

                Section::make('النشر')
                    ->schema([

                        Toggle::make('is_published')
                            ->label('منشور')
                            ->default(false),

                        DateTimePicker::make('published_at')
                            ->label('تاريخ النشر')
                            ->seconds(false)
                            ->helperText('عند تفعيل النشر، يظهر الخبر في هذا الموعد. اتركه فارغاً للنشر فوراً.'),

                        TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->required()
                            ->integer()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(4294967295),

                    ])
                    ->columns(3),

            ]);
    }
}