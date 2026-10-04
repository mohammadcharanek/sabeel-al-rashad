<?php

namespace App\Filament\Resources\EducationalStages\Pages;

use App\Filament\Resources\EducationalStages\EducationalStageResource;
use App\Models\EducationalStage;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditEducationalStage extends EditRecord
{
    protected static string $resource = EducationalStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->before(function (DeleteAction $action, EducationalStage $record): void {
                    if ($reason = $record->deletionBlockReason()) {
                        Notification::make()->danger()->title('تعذر حذف المرحلة')->body($reason)->send();
                        $action->cancel();
                    }
                }),
        ];
    }
}
