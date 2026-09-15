<?php

namespace App\Filament\Resources\EducationalStages\Pages;

use App\Filament\Resources\EducationalStages\EducationalStageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEducationalStage extends ViewRecord
{
    protected static string $resource = EducationalStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
