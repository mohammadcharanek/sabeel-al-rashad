<?php

namespace App\Filament\Resources\GalleryItems\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GalleryItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image')->label('الصورة')->disk('public'),
            TextColumn::make('title')->label('العنوان')->searchable(),
            TextColumn::make('folder.title')->label('المجلد')->placeholder('غير مصنفة'),
            TextColumn::make('alt_text')->label('الوصف البديل')->limit(45),
            TextColumn::make('sort_order')->label('الترتيب')->sortable(),
            ToggleColumn::make('is_featured')->label('رئيسية'),
            IconColumn::make('is_active')->label('منشورة')->boolean(),
            TextColumn::make('updated_at')->label('آخر تعديل')->dateTime('Y-m-d H:i')->sortable(),
        ])->defaultSort('sort_order')->reorderable('sort_order')->filters([
            SelectFilter::make('media_folder_id')->label('المجلد')
                ->relationship('folder', 'title', fn (Builder $query): Builder => $query->where('media_type', 'photo')),
            TernaryFilter::make('is_active')->label('حالة النشر')->trueLabel('منشورة')->falseLabel('غير منشورة')->placeholder('الكل'),
        ])->recordActions([EditAction::make()]);
    }
}
