<?php

namespace App\Filament\Resources\Statistics\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class StatisticsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('value')->label('القيمة')->searchable(),
                TextColumn::make('label')->label('الوصف')->searchable(),
                TextColumn::make('sort_order')->label('الترتيب')->sortable(),
                ToggleColumn::make('is_active')->label('منشورة'),
                TextColumn::make('updated_at')->label('آخر تعديل')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('حالة النشر')
                    ->trueLabel('منشورة')
                    ->falseLabel('غير منشورة')
                    ->placeholder('الكل'),
            ])
            ->recordActions([EditAction::make()]);
    }
}
