<?php

namespace App\Filament\Resources\EducationalStages;

use App\Filament\Resources\EducationalStages\Pages\CreateEducationalStage;
use App\Filament\Resources\EducationalStages\Pages\EditEducationalStage;
use App\Filament\Resources\EducationalStages\Pages\ListEducationalStages;
use App\Filament\Resources\EducationalStages\Pages\ViewEducationalStage;
use App\Filament\Resources\EducationalStages\Schemas\EducationalStageForm;
use App\Filament\Resources\EducationalStages\Schemas\EducationalStageInfolist;
use App\Filament\Resources\EducationalStages\Tables\EducationalStagesTable;
use App\Models\EducationalStage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EducationalStageResource extends Resource
{
    protected static ?string $model = EducationalStage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'المراحل التعليمية';

    protected static ?string $modelLabel = 'مرحلة تعليمية';

    protected static ?string $pluralModelLabel = 'المراحل التعليمية';

    protected static string|UnitEnum|null $navigationGroup = 'إدارة المحتوى';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return EducationalStageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EducationalStageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EducationalStagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEducationalStages::route('/'),
            'create' => CreateEducationalStage::route('/create'),
            'view' => ViewEducationalStage::route('/{record}'),
            'edit' => EditEducationalStage::route('/{record}/edit'),
        ];
    }
}