<?php

use App\Filament\Resources\Features\Pages\CreateFeature;
use App\Models\Feature;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('only active features appear on homepage in approved order', function () {
    $later = Feature::query()->create([
        'title' => 'ميزة متأخرة', 'description' => 'وصف متأخر', 'icon' => 'academic', 'sort_order' => 2, 'is_active' => true,
    ]);
    $first = Feature::query()->create([
        'title' => 'ميزة أولى', 'description' => 'وصف أول', 'icon' => 'safe', 'sort_order' => 1, 'is_active' => true,
    ]);
    Feature::query()->create([
        'title' => 'ميزة مخفية', 'sort_order' => 0, 'is_active' => false,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertViewHas('features', fn ($items) => $items->modelKeys() === [$first->id, $later->id])
        ->assertSeeInOrder(['ميزة أولى', 'ميزة متأخرة'])
        ->assertSee('وصف أول')
        ->assertDontSee('ميزة مخفية');
});

test('feature text is escaped on the homepage', function () {
    Feature::query()->create([
        'title' => '<script>unsafe</script>', 'description' => '<img src=x onerror=alert(1)>', 'icon' => 'safe', 'is_active' => true,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('<script>unsafe</script>')
        ->assertDontSee('<script>unsafe</script>', false)
        ->assertDontSee('<img src=x onerror=alert(1)>', false);
});

test('empty features show a neutral message instead of hard-coded claims', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('ستُعرض مميزات المدرسة هنا بعد اعتماد محتواها.')
        ->assertDontSee('مناهج دراسية متطورة ومعتمدة');
});

test('feature admin resource denies non administrators', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(User::factory()->create());
    $this->get('/admin/features')->assertForbidden();
});

test('administrator creates an unpublished feature by default', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateFeature::class)
        ->fillForm(['title' => 'ميزة تجريبية', 'description' => 'وصف تجريبي', 'icon' => 'safe', 'sort_order' => 1])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('features', [
        'title' => 'ميزة تجريبية', 'is_active' => false,
    ]);
});
