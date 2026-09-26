<?php

namespace App\Filament\Resources\StudentApplications\Pages;

use App\Filament\Resources\StudentApplications\StudentApplicationResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditStudentApplication extends EditRecord
{
    protected static string $resource = StudentApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make()];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update(Arr::only($data, ['status', 'admin_note']));

        return $record;
    }
}
