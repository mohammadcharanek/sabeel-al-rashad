<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('معلومات الفعالية')
                    ->schema([

                        TextEntry::make('title')
                            ->label('عنوان الفعالية'),

                        TextEntry::make('slug')
                            ->label('الرابط المختصر')
                            ->copyable(),

                        TextEntry::make('description')
                            ->label('وصف الفعالية')
                            ->placeholder('لا يوجد وصف')
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

                Section::make('التاريخ والمكان')
                    ->schema([

                        TextEntry::make('starts_at')
                            ->label('تاريخ ووقت البداية')
                            ->dateTime('Y-m-d H:i'),

                        TextEntry::make('ends_at')
                            ->label('تاريخ ووقت النهاية')
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('غير محدد'),

                        TextEntry::make('location')
                            ->label('المكان')
                            ->placeholder('غير محدد'),

                    ])
                    ->columns(3),

                Section::make('الصورة')
                    ->schema([

                        ImageEntry::make('image')
                            ->label('صورة الفعالية')
                            ->disk('public')
                            ->height(220)
                            ->placeholder('لا توجد صورة')
                            ->columnSpanFull(),

                    ]),

                Section::make('النشر والترتيب')
                    ->schema([

                        IconEntry::make('is_published')
                            ->label('حالة النشر')
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