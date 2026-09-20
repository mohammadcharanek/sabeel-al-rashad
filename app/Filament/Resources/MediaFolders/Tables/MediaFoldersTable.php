<?php

namespace App\Filament\Resources\MediaFolders\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MediaFoldersTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label('العنوان')->searchable(),
            TextColumn::make('media_type')->label('نوع الوسائط')->formatStateUsing(fn (string $state): string => $state === 'photo' ? 'صور' : 'فيديو'),
            TextColumn::make('photos_count')->label('الصور')->counts('photos'),
            TextColumn::make('videos_count')->label('الفيديوهات')->counts('videos'),
            TextColumn::make('sort_order')->label('الترتيب')->sortable(),
            IconColumn::make('is_published')->label('منشور')->boolean(),
        ])->defaultSort('sort_order')->reorderable('sort_order')->filters([
            SelectFilter::make('media_type')->label('نوع الوسائط')->options(['photo' => 'صور', 'video' => 'فيديو']),
            TernaryFilter::make('is_published')->label('حالة النشر'),
        ])->recordActions([EditAction::make()]);
    }
}
