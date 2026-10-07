<?php

namespace App\Filament\Resources\StudentApplications\Schemas;

use App\Models\StudentApplication;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الطالب')->schema([
                TextEntry::make('reference_number')->label('رقم المرجع')->copyable(),
                TextEntry::make('status')->label('حالة الطلب')->formatStateUsing(fn (string $state): string => StudentApplication::STATUSES[$state])->badge(),
                TextEntry::make('student_name')->label('اسم الطالب الثلاثي'),
                TextEntry::make('date_of_birth')->label('تاريخ الميلاد')->date('Y-m-d'),
                TextEntry::make('educationalGrade.name_ar')->label('الصف المطلوب')->placeholder('الصف غير محدد في الطلب القديم'),
                TextEntry::make('educationalStage.title')->label('المرحلة التعليمية'),
                TextEntry::make('educationalStage.educationSystem.name')->label('نظام التعليم')->placeholder('غير محدد في الطلب القديم'),
                TextEntry::make('registration_type')->label('حالة الطالب')
                    ->formatStateUsing(fn (string $state): string => StudentApplication::REGISTRATION_TYPES[$state] ?? 'غير محددة'),
                TextEntry::make('document_requirement')->label('متطلب المستند')
                    ->state(fn (StudentApplication $record): string => $record->document_required === null
                        ? 'يلزم تصنيف المرحلة لتحديد المتطلبات'
                        : $record->requirements()['document_summary']),
                TextEntry::make('interview_requirement')->label('المقابلة')->state(fn (StudentApplication $record): string => $record->requirements()['interview_summary']),
                TextEntry::make('exam_requirement')->label('امتحان الدخول')
                    ->state(fn (StudentApplication $record): string => $record->entrance_exam_required === null
                        ? 'يلزم تصنيف المرحلة لتحديد المتطلبات'
                        : $record->requirements()['exam_summary']),
            ])->columns(2)->columnSpanFull(),
            Section::make('بيانات ولي الأمر')->schema([
                TextEntry::make('guardian_name')->label('اسم ولي الأمر'),
                TextEntry::make('guardian_phone')->label('هاتف ولي الأمر')->copyable(),
                TextEntry::make('guardian_email')->label('البريد الإلكتروني')->placeholder('غير مرفق'),
                TextEntry::make('notes')->label('ملاحظات ولي الأمر')->placeholder('لا توجد ملاحظات')->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
            Section::make('المستند والمراجعة')->schema([
                TextEntry::make('document_type')->label('نوع المستند المصرح به')
                    ->formatStateUsing(fn (string $state): string => StudentApplication::DOCUMENT_TYPES[$state] ?? 'غير محدد')
                    ->placeholder('غير مسجل في الطلب القديم'),
                TextEntry::make('foreign_document_attestation')->label('تصديق الإفادة من لبنان')
                    ->visible(fn (StudentApplication $record): bool => $record->registration_type === 'traveler')
                    ->state(fn (StudentApplication $record): string => match ($record->foreign_document_attestation_confirmed) {
                        true => 'أكد مقدم الطلب التصديق من لبنان — يلزم مراجعة المستند',
                        false => 'لم يؤكد مقدم الطلب التصديق من لبنان',
                        null => 'غير مسجل في الطلب القديم',
                    }),
                TextEntry::make('document_original_name')
                    ->label('المستند المرفق')
                    ->placeholder('لا يوجد مستند')
                    ->url(fn (StudentApplication $record): ?string => $record->document_path ? route('student-applications.document', $record) : null),
                TextEntry::make('admin_note')->label('ملاحظة إدارية داخلية')->placeholder('لا توجد ملاحظات')->columnSpanFull(),
                TextEntry::make('submitted_at')->label('تاريخ التقديم')->dateTime('Y-m-d H:i'),
                TextEntry::make('created_at')->label('تاريخ الإنشاء')->dateTime('Y-m-d H:i'),
                TextEntry::make('updated_at')->label('آخر تعديل')->dateTime('Y-m-d H:i'),
            ])->columns(2)->columnSpanFull(),
        ]);
    }
}
