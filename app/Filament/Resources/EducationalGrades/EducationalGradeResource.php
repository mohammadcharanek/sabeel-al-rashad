<?php

namespace App\Filament\Resources\EducationalGrades;

use App\Filament\Resources\EducationalGrades\Pages\CreateEducationalGrade;
use App\Filament\Resources\EducationalGrades\Pages\EditEducationalGrade;
use App\Filament\Resources\EducationalGrades\Pages\ListEducationalGrades;
use App\Filament\Resources\EducationalGrades\Schemas\EducationalGradeForm;
use App\Filament\Resources\EducationalGrades\Tables\EducationalGradesTable;
use App\Models\EducationalGrade;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EducationalGradeResource extends Resource
{
    protected static ?string $model = EducationalGrade::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name_ar';

    protected static ?string $modelLabel = 'صف تعليمي';

    protected static ?string $pluralModelLabel = 'الصفوف التعليمية';

    protected static string|UnitEnum|null $navigationGroup = 'إدارة المحتوى';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return EducationalGradeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EducationalGradesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEducationalGrades::route('/'),
            'create' => CreateEducationalGrade::route('/create'),
            'edit' => EditEducationalGrade::route('/{record}/edit'),
        ];
    }
}
