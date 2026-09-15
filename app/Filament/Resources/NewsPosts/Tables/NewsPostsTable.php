<?php

namespace App\Filament\Resources\NewsPosts\Tables;

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

class NewsPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                ImageColumn::make('featured_image')
                    ->label('الصورة')
                    ->disk('public')
                    ->visibility('public')
                    ->square()
                    ->size(55),

                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable()
                    ->limit(45),

                TextColumn::make('category')
                    ->label('التصنيف')
                    ->badge()
                    ->searchable(),

                TextColumn::make('published_at')
                    ->label('تاريخ النشر')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->placeholder('غير محدد'),

                ToggleColumn::make('is_published')
                    ->label('منشور'),

                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('آخر تعديل')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->defaultSort('published_at', 'desc')
            ->filters([

                SelectFilter::make('category')
                    ->label('التصنيف')
                    ->options([
                        'أخبار المدرسة' => 'أخبار المدرسة',
                        'الأنشطة الرياضية' => 'الأنشطة الرياضية',
                        'الإنجازات الأكاديمية' => 'الإنجازات الأكاديمية',
                        'الفعاليات المدرسية' => 'الفعاليات المدرسية',
                        'إعلانات' => 'إعلانات',
                    ]),

                TernaryFilter::make('is_published')
                    ->label('حالة النشر'),

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