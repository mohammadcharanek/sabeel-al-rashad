<?php

namespace App\Filament\Resources\EducationalGrades\Pages;

use App\Filament\Resources\EducationalGrades\EducationalGradeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEducationalGrade extends EditRecord
{
    protected static string $resource = EducationalGradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
