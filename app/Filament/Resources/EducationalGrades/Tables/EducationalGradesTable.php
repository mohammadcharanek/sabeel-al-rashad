<?php

namespace App\Filament\Resources\EducationalGrades\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EducationalGradesTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('sort_order')->columns([
            TextColumn::make('name_ar')->label('الصف')->searchable(),
            TextColumn::make('code')->label('الرمز الثابت'),
            TextColumn::make('educationalStage.title')->label('المرحلة التعليمية'),
            TextColumn::make('educationalStage.educationSystem.name')->label('نظام التعليم'),
            TextColumn::make('sort_order')->label('الترتيب')->sortable(),
            IconColumn::make('is_active')->label('متاح للتسجيل')->boolean(),
        ])->filters([
            SelectFilter::make('educational_stage_id')->label('المرحلة التعليمية')->relationship('educationalStage', 'title'),
            TernaryFilter::make('is_active')->label('متاح للتسجيل'),
        ])->recordActions([EditAction::make()]);
    }
}
