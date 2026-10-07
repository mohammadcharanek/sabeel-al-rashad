<?php

namespace App\Filament\Resources\EducationSystems\Pages;

use App\Filament\Resources\EducationSystems\EducationSystemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEducationSystems extends ListRecords
{
    protected static string $resource = EducationSystemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
