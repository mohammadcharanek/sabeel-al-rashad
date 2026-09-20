<?php

namespace App\Filament\Resources\Videos\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VideosTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label('العنوان')->searchable(),
            TextColumn::make('folder.title')->label('المجلد')->placeholder('غير مصنف'),
            TextColumn::make('sort_order')->label('الترتيب')->sortable(),
            IconColumn::make('is_published')->label('منشور')->boolean(),
            TextColumn::make('updated_at')->label('آخر تعديل')->dateTime('Y-m-d H:i')->sortable(),
        ])->defaultSort('sort_order')->reorderable('sort_order')->filters([
            SelectFilter::make('media_folder_id')->label('المجلد')
                ->relationship('folder', 'title', fn (Builder $query): Builder => $query->where('media_type', 'video')),
            TernaryFilter::make('is_published')->label('حالة النشر'),
        ])->recordActions([EditAction::make()]);
    }
}
