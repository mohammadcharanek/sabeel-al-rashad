<?php

namespace App\Filament\Resources\Testimonials\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TestimonialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('person_name')->label('الاسم المعروض')->searchable()->sortable(),
                TextColumn::make('quote')->label('نص الرأي')->limit(80)->searchable()->wrap(),
                TextColumn::make('person_role')->label('الصفة')->searchable()->sortable(),
                IconColumn::make('is_approved')->label('معتمد بموافقة صاحبه')->boolean()->sortable(),
                IconColumn::make('is_published')->label('منشور')->boolean()->sortable(),
                TextColumn::make('sort_order')->label('الترتيب')->sortable(),
                TextColumn::make('updated_at')->label('آخر تعديل')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query->orderBy('sort_order')->orderBy('id'))
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('حالة النشر')->trueLabel('منشور')->falseLabel('غير منشور')->placeholder('الكل'),
                TernaryFilter::make('is_approved')
                    ->label('الاعتماد والموافقة')->trueLabel('معتمد')->falseLabel('بانتظار الاعتماد')->placeholder('الكل'),
            ])
            ->recordActions([EditAction::make()]);
    }
}
