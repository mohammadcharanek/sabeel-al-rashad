<?php

namespace App\Filament\Resources\StudentApplications\Schemas;

use App\Models\StudentApplication;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class StudentApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('status')
                ->label('حالة الطلب')
                ->options(StudentApplication::STATUSES)
                ->rules([Rule::in(array_keys(StudentApplication::STATUSES))])
                ->required(),
            Textarea::make('admin_note')
                ->label('ملاحظة إدارية داخلية')
                ->helperText('هذه الملاحظة متاحة للإدارة فقط.')
                ->maxLength(5000)
                ->rows(5)
                ->columnSpanFull(),
        ]);
    }
}
