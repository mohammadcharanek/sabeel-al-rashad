<?php

namespace App\Filament\Resources\MediaFolders;

use App\Filament\Resources\MediaFolders\Pages\CreateMediaFolder;
use App\Filament\Resources\MediaFolders\Pages\EditMediaFolder;
use App\Filament\Resources\MediaFolders\Pages\ListMediaFolders;
use App\Filament\Resources\MediaFolders\Schemas\MediaFolderForm;
use App\Filament\Resources\MediaFolders\Tables\MediaFoldersTable;
use App\Models\MediaFolder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MediaFolderResource extends Resource
{
    protected static ?string $model = MediaFolder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'مجلدات الوسائط';

    protected static ?string $modelLabel = 'مجلد';

    protected static ?string $pluralModelLabel = 'مجلدات الوسائط';

    protected static string|UnitEnum|null $navigationGroup = 'إدارة المحتوى';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return MediaFolderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MediaFoldersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMediaFolders::route('/'),
            'create' => CreateMediaFolder::route('/create'),
            'edit' => EditMediaFolder::route('/{record}/edit'),
        ];
    }
}
