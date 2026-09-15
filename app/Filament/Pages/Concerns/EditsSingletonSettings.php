<?php

namespace App\Filament\Pages\Concerns;

use App\Models\SingletonSetting;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Illuminate\Support\Facades\Gate;

trait EditsSingletonSettings
{
    use RestrictsFileUploadsToSchemaComponents;

    /** @var array<string, mixed> */
    public array $data = [];

    abstract protected function settings(): SingletonSetting;

    public static function canAccess(): bool
    {
        return Gate::allows('manage-settings');
    }

    public function mount(): void
    {
        Gate::authorize('manage-settings');

        $this->form->fill($this->settingsFormData($this->settings()));
    }

    public function save(): void
    {
        Gate::authorize('manage-settings');

        $data = $this->form->getState();
        $settings = $this->settings();
        $settings = $settings::query()->updateOrCreate(['singleton_key' => 'global'], $data);

        $this->form->model($settings)->fill($this->settingsFormData($settings));

        Notification::make()->title('تم حفظ الإعدادات')->success()->send();
    }

    /** @return array<string, mixed> */
    protected function settingsFormData(SingletonSetting $settings): array
    {
        return $settings->attributesToArray();
    }
}
