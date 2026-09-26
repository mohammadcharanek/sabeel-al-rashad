<?php

namespace App\Filament\Resources\StudentApplications\Pages;

use App\Filament\Resources\StudentApplications\StudentApplicationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStudentApplication extends ViewRecord
{
    protected static string $resource = StudentApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
