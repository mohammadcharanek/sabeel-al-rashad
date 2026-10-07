<?php

namespace App\Filament\Resources\EducationSystems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class EducationSystemsTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')->label('نظام التعليم')->searchable(),
                TextColumn::make('slug')->label('الرمز'),
                TextColumn::make('educational_stages_count')->counts('educationalStages')->label('عدد المراحل'),
                TextColumn::make('sort_order')->label('الترتيب')->sortable(),
                IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->filters([TernaryFilter::make('is_active')->label('نشط')])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->before(function (DeleteBulkAction $action, Collection $records): void {
                        foreach ($records as $record) {
                            if ($reason = $record->deletionBlockReason()) {
                                Notification::make()->danger()->title('تعذر حذف أنظمة التعليم المحددة')->body($reason)->send();
                                $action->cancel();
                            }
                        }
                    }),
                ]),
            ]);
    }
}
