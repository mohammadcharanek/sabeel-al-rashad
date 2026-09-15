<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                ImageColumn::make('image')
                    ->label('الصورة')
                    ->disk('public')
                    ->square()
                    ->size(55),

                TextColumn::make('title')
                    ->label('عنوان الفعالية')
                    ->searchable()
                    ->sortable()
                    ->limit(45),

                TextColumn::make('starts_at')
                    ->label('تاريخ البداية')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('تاريخ النهاية')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->placeholder('غير محدد'),

                TextColumn::make('location')
                    ->label('المكان')
                    ->searchable()
                    ->placeholder('غير محدد'),

                ToggleColumn::make('is_published')
                    ->label('منشورة'),

                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('الرابط المختصر')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('آخر تعديل')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->defaultSort('starts_at')
            ->filters([

                TernaryFilter::make('is_published')
                    ->label('حالة النشر')
                    ->trueLabel('منشورة')
                    ->falseLabel('غير منشورة')
                    ->placeholder('الكل'),

            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}