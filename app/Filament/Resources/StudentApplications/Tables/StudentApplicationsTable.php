<?php

namespace App\Filament\Resources\StudentApplications\Tables;

use App\Models\StudentApplication;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('submitted_at', 'desc')
            ->columns([
                TextColumn::make('reference_number')->label('رقم المرجع')->searchable()->copyable(),
                TextColumn::make('student_name')->label('اسم الطالب')->searchable(),
                TextColumn::make('guardian_name')->label('ولي الأمر')->searchable(),
                TextColumn::make('guardian_phone')->label('الهاتف')->searchable(),
                TextColumn::make('educationalStage.title')->label('المرحلة التعليمية'),
                TextColumn::make('status')->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => StudentApplication::STATUSES[$state])
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'under_review' => 'info',
                        default => 'warning',
                    }),
                TextColumn::make('submitted_at')->label('تاريخ التقديم')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('الحالة')->options(StudentApplication::STATUSES),
                SelectFilter::make('educational_stage_id')->label('المرحلة التعليمية')->relationship('educationalStage', 'title'),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()]);
    }
}
