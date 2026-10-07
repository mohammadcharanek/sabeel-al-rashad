<?php

namespace App\Filament\Resources\EducationSystems;

use App\Filament\Resources\EducationSystems\Pages\CreateEducationSystem;
use App\Filament\Resources\EducationSystems\Pages\EditEducationSystem;
use App\Filament\Resources\EducationSystems\Pages\ListEducationSystems;
use App\Filament\Resources\EducationSystems\Schemas\EducationSystemForm;
use App\Filament\Resources\EducationSystems\Tables\EducationSystemsTable;
use App\Models\EducationSystem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EducationSystemResource extends Resource
{
    protected static ?string $model = EducationSystem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'نظام تعليم';

    protected static ?string $pluralModelLabel = 'أنظمة التعليم';

    protected static string|UnitEnum|null $navigationGroup = 'إدارة المحتوى';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return EducationSystemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EducationSystemsTable::configure($table);
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
            'index' => ListEducationSystems::route('/'),
            'create' => CreateEducationSystem::route('/create'),
            'edit' => EditEducationSystem::route('/{record}/edit'),
        ];
    }
}
