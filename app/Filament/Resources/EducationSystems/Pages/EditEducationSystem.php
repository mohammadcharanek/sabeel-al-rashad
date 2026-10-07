<?php

namespace App\Filament\Resources\EducationSystems\Pages;

use App\Filament\Resources\EducationSystems\EducationSystemResource;
use App\Models\EducationSystem;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditEducationSystem extends EditRecord
{
    protected static string $resource = EducationSystemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->before(function (DeleteAction $action, EducationSystem $record): void {
                if ($reason = $record->deletionBlockReason()) {
                    Notification::make()->danger()->title('تعذر حذف نظام التعليم')->body($reason)->send();
                    $action->cancel();
                }
            }),
        ];
    }
}
