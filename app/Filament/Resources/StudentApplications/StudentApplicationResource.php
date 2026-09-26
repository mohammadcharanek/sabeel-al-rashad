<?php

namespace App\Filament\Resources\StudentApplications;

use App\Filament\Resources\StudentApplications\Pages\EditStudentApplication;
use App\Filament\Resources\StudentApplications\Pages\ListStudentApplications;
use App\Filament\Resources\StudentApplications\Pages\ViewStudentApplication;
use App\Filament\Resources\StudentApplications\Schemas\StudentApplicationForm;
use App\Filament\Resources\StudentApplications\Schemas\StudentApplicationInfolist;
use App\Filament\Resources\StudentApplications\Tables\StudentApplicationsTable;
use App\Models\StudentApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StudentApplicationResource extends Resource
{
    protected static ?string $model = StudentApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'طلبات التسجيل';

    protected static ?string $modelLabel = 'طلب تسجيل';

    protected static ?string $pluralModelLabel = 'طلبات التسجيل';

    protected static ?string $recordTitleAttribute = 'reference_number';

    public static function form(Schema $schema): Schema
    {
        return StudentApplicationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StudentApplicationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentApplicationsTable::configure($table);
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
            'index' => ListStudentApplications::route('/'),
            'view' => ViewStudentApplication::route('/{record}'),
            'edit' => EditStudentApplication::route('/{record}/edit'),
        ];
    }
}
