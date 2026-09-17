<?php

namespace App\Filament\Resources\GalleryItems\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class GalleryItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image')->label('الصورة')->disk('public'),
            TextColumn::make('title')->label('العنوان')->searchable(),
            TextColumn::make('alt_text')->label('الوصف البديل')->limit(45),
            TextColumn::make('sort_order')->label('الترتيب')->sortable(),
            ToggleColumn::make('is_featured')->label('رئيسية'),
            ToggleColumn::make('is_active')->label('منشورة'),
            TextColumn::make('updated_at')->label('آخر تعديل')->dateTime('Y-m-d H:i')->sortable(),
        ])->defaultSort('sort_order')->filters([
            TernaryFilter::make('is_active')->label('حالة النشر')->trueLabel('منشورة')->falseLabel('غير منشورة')->placeholder('الكل'),
        ])->recordActions([EditAction::make()]);
    }
}
