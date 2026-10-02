<?php

namespace App\Filament\Resources\EducationalGrades\Schemas;

use App\Models\EducationalGrade;
use App\Models\EducationalStage;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class EducationalGradeForm
{
    public static function configure(Schema $schema): Schema
    {
        $identityLocked = fn (?EducationalGrade $record): bool => $record?->studentApplications()->exists() ?? false;

        return $schema->components([
            TextInput::make('name_ar')->label('اسم الصف')->required()->maxLength(150)->disabled($identityLocked),
            TextInput::make('code')->label('الرمز الثابت')->required()->maxLength(30)->unique(ignoreRecord: true)
                ->regex('/\A(?:kg[123]|grade_[1-9][0-9]*)\z/')
                ->rules([
                    fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                        $stageId = $get('educational_stage_id');
                        $category = is_scalar($stageId) ? EducationalStage::whereKey($stageId)->value('category') : null;
                        if (! is_string($value) || ! EducationalGrade::codeMatchesCategory($value, $category)) {
                            $fail('رمز الصف لا يتوافق مع تصنيف المرحلة.');
                        }
                    },
                ])
                ->helperText('kg1 / kg2 / kg3 للروضات، وgrade_1 وما يليه للمراحل الأخرى. هوية الصف المرتبط بطلبات محمية من التعديل.')
                ->disabled($identityLocked),
            Select::make('educational_stage_id')->label('المرحلة التعليمية')
                ->relationship('educationalStage', 'title', modifyQueryUsing: fn (Builder $query): Builder => $query->whereIn('category', array_keys(EducationalStage::CATEGORIES)))
                ->required()->disabled($identityLocked),
            TextInput::make('sort_order')->label('الترتيب')->integer()->minValue(0)->default(0)->required(),
            Toggle::make('is_active')->label('متاح للتسجيل')->default(true),
        ]);
    }
}
