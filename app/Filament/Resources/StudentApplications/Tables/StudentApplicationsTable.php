<?php

namespace App\Filament\Resources\StudentApplications\Tables;

use App\Models\StudentApplication;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['educationalStage.educationSystem', 'educationalGrade.educationalStage']))
            ->defaultSort('submitted_at', 'desc')
            ->columns([
                TextColumn::make('reference_number')->label('رقم المرجع')->searchable()->copyable(),
                TextColumn::make('student_name')->label('اسم الطالب')->searchable(),
                TextColumn::make('guardian_name')->label('ولي الأمر')->searchable(),
                TextColumn::make('guardian_phone')->label('الهاتف')->searchable(),
                TextColumn::make('educationalGrade.name_ar')->label('الصف المطلوب')->placeholder('الصف غير محدد في الطلب القديم'),
                TextColumn::make('educationalStage.title')->label('المرحلة التعليمية'),
                TextColumn::make('educationalStage.educationSystem.name')->label('نظام التعليم')->placeholder('غير محدد'),
                TextColumn::make('registration_type')->label('حالة الطالب')
                    ->formatStateUsing(fn (string $state): string => StudentApplication::REGISTRATION_TYPES[$state] ?? 'غير محددة')->badge(),
                TextColumn::make('document_requirement')->label('متطلب المستند')
                    ->state(fn (StudentApplication $record): string => match ($record->document_required) {
                        true => 'مطلوب', false => 'اختياري', null => 'يلزم تصنيف المرحلة',
                    }),
                IconColumn::make('document_attached')->label('مستند مرفق')->boolean()
                    ->state(fn (StudentApplication $record): bool => filled($record->document_path)),
                TextColumn::make('interview_requirement')->label('المقابلة')
                    ->state(fn (StudentApplication $record): string => match ($record->interview_required) {
                        true => 'مطلوب', false => 'غير مطلوب', null => 'غير محدد',
                    }),
                TextColumn::make('exam_requirement')->label('امتحان الدخول')
                    ->state(fn (StudentApplication $record): string => match ($record->entrance_exam_required) {
                        true => 'مطلوب', false => 'غير مطلوب', null => 'يلزم تصنيف المرحلة',
                    }),
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
                SelectFilter::make('registration_type')->label('حالة الطالب')->options(StudentApplication::REGISTRATION_TYPES),
                TernaryFilter::make('entrance_exam_required')->label('امتحان الدخول')
                    ->trueLabel('مطلوب')->falseLabel('غير مطلوب')->placeholder('الكل')
                    ->queries(
                        true: fn (Builder $query): Builder => StudentApplication::filterByEntranceExamRequirement($query, true),
                        false: fn (Builder $query): Builder => StudentApplication::filterByEntranceExamRequirement($query, false),
                    ),
                SelectFilter::make('educational_grade_id')->label('الصف المطلوب')->relationship('educationalGrade', 'name_ar'),
                TernaryFilter::make('interview_required')->label('المقابلة')
                    ->trueLabel('مطلوب')->falseLabel('غير مطلوب')->placeholder('الكل')
                    ->queries(
                        true: fn (Builder $query): Builder => StudentApplication::filterByRequirement($query, 'interview_required', true),
                        false: fn (Builder $query): Builder => StudentApplication::filterByRequirement($query, 'interview_required', false),
                    ),
                SelectFilter::make('status')->label('الحالة')->options(StudentApplication::STATUSES),
                SelectFilter::make('educational_stage_id')->label('المرحلة التعليمية')->relationship('educationalStage', 'title'),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()]);
    }
}
