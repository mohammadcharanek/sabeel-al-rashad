<?php

use App\Filament\Resources\Statistics\Pages\CreateStatistic;
use App\Models\Statistic;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('statistics show a neutral state rather than unverified default figures', function () {
    $response = $this->get('/');

    $response->assertOk();

    $html = $response->getContent();

    preg_match(
        '/<section\b[^>]*\bid="statistics"[^>]*>(.*?)<\/section>/s',
        $html,
        $matches
    );

    expect($matches)->toHaveKey(1);

    $statisticsHtml = $matches[1];

    expect($statisticsHtml)
        ->toContain('ستُعرض إحصائيات المدرسة هنا بعد اعتماد البيانات الرسمية.')
        ->not->toContain('+٥٠٠', '+٤٠');
});

test('only active statistics appear in approved order and their content is escaped', function () {
    $late = Statistic::query()->create([
        'value' => '١٥٠', 'label' => 'الطلاب', 'icon' => 'users', 'sort_order' => 2, 'is_active' => true,
    ]);
    $early = Statistic::query()->create([
        'value' => '٣٠', 'label' => '<script>unsafe</script>', 'icon' => 'teachers', 'sort_order' => 1, 'is_active' => true,
    ]);
    $hidden = Statistic::query()->create([
        'value' => '٩٩٩', 'label' => 'غير معتمدة', 'sort_order' => 0, 'is_active' => false,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertViewHas('statistics', fn ($items) => $items->modelKeys() === [$early->id, $late->id])
        ->assertSeeInOrder(['٣٠', '١٥٠'])
        ->assertSee($early->label)
        ->assertDontSee($early->label, false)
        ->assertDontSee($hidden->value)
        ->assertDontSee('ستُعرض إحصائيات المدرسة هنا بعد اعتماد البيانات الرسمية.');
});

test('statistics admin resource requires admin access', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $this->actingAs(User::factory()->create());
    $this->get('/admin/statistics')->assertForbidden();
});

test('administrator creates an unpublished statistic by default', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateStatistic::class)
        ->fillForm(['value' => '١٥٠', 'label' => 'طالب', 'icon' => 'users', 'sort_order' => 1])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('statistics', [
        'value' => '١٥٠', 'label' => 'طالب', 'is_active' => false,
    ]);
});
