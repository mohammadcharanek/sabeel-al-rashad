<?php

namespace App\Filament\Resources\EducationalStages\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EducationalStageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('معلومات المرحلة')
                    ->schema([

                        TextEntry::make('title')
                            ->label('اسم المرحلة'),

                        TextEntry::make('slug')
                            ->label('الرابط المختصر')
                            ->copyable(),

                        TextEntry::make('description')
                            ->label('الوصف')
                            ->placeholder('لا يوجد وصف')
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

                Section::make('المظهر')
                    ->schema([

                        TextEntry::make('icon')
                            ->label('الأيقونة')
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'elementary' => 'المرحلة الابتدائية',
                                'middle' => 'المرحلة المتوسطة',
                                'secondary' => 'المرحلة الثانوية',
                                default => 'غير محددة',
                            })
                            ->badge(),

                        ImageEntry::make('image')
                            ->label('صورة المرحلة')
                            ->disk('public')
                            ->height(220)
                            ->placeholder('لا توجد صورة'),

                    ])
                    ->columns(2),

                Section::make('الرابط')
                    ->schema([

                        TextEntry::make('link_label')
                            ->label('نص الرابط')
                            ->placeholder('غير محدد'),

                        TextEntry::make('link_url')
                            ->label('الرابط')
                            ->placeholder('غير محدد')
                            ->copyable(),

                    ])
                    ->columns(2),

                Section::make('الظهور والترتيب')
                    ->schema([

                        IconEntry::make('is_active')
                            ->label('ظاهر في الموقع')
                            ->boolean(),

                        TextEntry::make('sort_order')
                            ->label('الترتيب')
                            ->numeric(),

                    ])
                    ->columns(2),

                Section::make('معلومات النظام')
                    ->collapsed()
                    ->schema([

                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('غير محدد'),

                        TextEntry::make('updated_at')
                            ->label('آخر تعديل')
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('غير محدد'),

                    ])
                    ->columns(2),

            ]);
    }
}