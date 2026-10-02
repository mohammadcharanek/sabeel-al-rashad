<?php

namespace App\Filament\Resources\EducationalGrades\Pages;

use App\Filament\Resources\EducationalGrades\EducationalGradeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEducationalGrades extends ListRecords
{
    protected static string $resource = EducationalGradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
