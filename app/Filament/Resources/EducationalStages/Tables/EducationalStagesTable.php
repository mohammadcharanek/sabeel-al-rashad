<?php

namespace App\Filament\Resources\EducationalStages\Tables;

use App\Models\EducationalStage;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EducationalStagesTable
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
                    ->label('اسم المرحلة')
                    ->searchable()
                    ->sortable()
                    ->limit(45),

                TextColumn::make('category')
                    ->label('تصنيف التسجيل')
                    ->formatStateUsing(
                        fn (?string $state): string => EducationalStage::CATEGORIES[$state] ?? 'غير مصنفة'
                    )
                    ->placeholder('غير مصنفة')
                    ->badge(),

                TextColumn::make('icon')
                    ->label('الأيقونة')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'kindergarten' => 'الروضات',
                        'elementary' => 'المرحلة الابتدائية',
                        'middle' => 'المرحلة المتوسطة',
                        'secondary' => 'المرحلة الثانوية',
                        default => 'غير محددة',
                    })
                    ->badge(),

                TextColumn::make('link_label')
                    ->label('نص الرابط')
                    ->placeholder('غير محدد')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('link_url')
                    ->label('الرابط')
                    ->limit(35)
                    ->placeholder('غير محدد')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('ظاهر في الموقع'),

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
            ->defaultSort('sort_order')
            ->filters([

                SelectFilter::make('category')
                    ->label('تصنيف التسجيل')
                    ->options(EducationalStage::CATEGORIES),

                TernaryFilter::make('is_active')
                    ->label('حالة الظهور')
                    ->trueLabel('ظاهرة')
                    ->falseLabel('مخفية')
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
