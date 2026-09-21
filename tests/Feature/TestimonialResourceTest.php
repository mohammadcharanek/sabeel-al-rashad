<?php

use App\Filament\Resources\Testimonials\Pages\CreateTestimonial;
use App\Filament\Resources\Testimonials\Pages\EditTestimonial;
use App\Filament\Resources\Testimonials\Pages\ListTestimonials;
use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Models\Testimonial;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

beforeEach(function () {
    config(['app.env' => 'production']);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('guests must sign in to testimonial pages', function (string $page) {
    $record = Testimonial::factory()->create();

    $this->get(TestimonialResource::getUrl($page, ['record' => $record->id]))
        ->assertRedirect(route('filament.admin.auth.login'));
})->with(['index', 'create', 'edit']);

test('testimonial pages enforce administrator access', function (bool $isAdmin, string $page) {
    $this->actingAs(User::factory()->create(['is_admin' => $isAdmin]));
    $record = Testimonial::factory()->create();

    $response = $this->get(TestimonialResource::getUrl($page, ['record' => $record->id]));

    $isAdmin ? $response->assertOk() : $response->assertForbidden();
})->with([true, false])->with(['index', 'create', 'edit']);

test('testimonial policy restricts every record ability to administrators', function (bool $isAdmin) {
    $user = User::factory()->create(['is_admin' => $isAdmin]);
    $record = Testimonial::factory()->create();

    foreach (['viewAny', 'create', 'deleteAny', 'reorder'] as $ability) {
        expect(Gate::forUser($user)->allows($ability, Testimonial::class))->toBe($isAdmin);
    }
    foreach (['view', 'update', 'delete'] as $ability) {
        expect(Gate::forUser($user)->allows($ability, $record))->toBe($isAdmin);
    }
})->with([true, false]);

test('guests and non administrators cannot mount testimonial Livewire pages', function (bool $signedIn, string $page) {
    $record = Testimonial::factory()->create();
    if ($signedIn) {
        $this->actingAs(User::factory()->create());
    }

    Livewire::test($page, $page === EditTestimonial::class ? ['record' => $record->id] : [])->assertForbidden();

    $this->assertDatabaseCount('testimonials', 1);
})->with([true, false])->with([CreateTestimonial::class, EditTestimonial::class, ListTestimonials::class]);

test('testimonial create actions recheck authorization after loss of access', function (bool $loggedOut) {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);
    $component = Livewire::test(CreateTestimonial::class)
        ->fillForm(['quote' => 'Unauthorized creation', 'person_name' => 'Test name', 'is_published' => true, 'is_approved' => true]);
    if ($loggedOut) {
        auth()->logout();
    } else {
        $admin->is_admin = false;
        $admin->save();
        $this->actingAs($admin->fresh());
    }

    $component->call('create')->assertForbidden();

    $this->assertDatabaseCount('testimonials', 0);
})->with(['logged out' => true, 'administrator revoked' => false]);

test('testimonial save actions recheck authorization after loss of access', function (bool $loggedOut) {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);
    $record = Testimonial::factory()->create();
    $original = $record->getRawOriginal();
    $component = Livewire::test(EditTestimonial::class, ['record' => $record->id])
        ->fillForm(['quote' => 'Unauthorized edit', 'is_published' => true, 'is_approved' => true]);
    if ($loggedOut) {
        auth()->logout();
    } else {
        $admin->is_admin = false;
        $admin->save();
        $this->actingAs($admin->fresh());
    }

    $component->call('save')->assertForbidden();

    $this->assertDatabaseHas('testimonials', $original);
})->with(['logged out' => true, 'administrator revoked' => false]);

test('administrator creates a draft without approval or attribution by default', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateTestimonial::class)
        ->fillForm(['quote' => 'نص اختبار غير منشور', 'person_name' => 'اسم اختبار'])
        ->call('create')->assertHasNoFormErrors();

    $this->assertDatabaseHas('testimonials', [
        'quote' => 'نص اختبار غير منشور', 'person_name' => 'اسم اختبار',
        'person_role' => null, 'sort_order' => 0, 'is_published' => false, 'is_approved' => false,
    ]);
    $this->get('/')->assertDontSee('نص اختبار غير منشور');
});

test('administrator approves publishes edits and unpublishes a testimonial', function () {
    $this->actingAs(User::factory()->admin()->create());
    $record = Testimonial::factory()->create();

    Livewire::test(EditTestimonial::class, ['record' => $record->id])
        ->fillForm([
            'quote' => 'نص معتمد للاختبار', 'person_name' => 'اسم معتمد للاختبار',
            'person_role' => 'ولي أمر', 'sort_order' => 3, 'is_approved' => true, 'is_published' => true,
        ])
        ->call('save')->assertHasNoFormErrors();

    $this->assertDatabaseHas('testimonials', [
        'id' => $record->id, 'quote' => 'نص معتمد للاختبار', 'person_name' => 'اسم معتمد للاختبار',
        'person_role' => 'ولي أمر', 'sort_order' => 3, 'is_approved' => true, 'is_published' => true,
    ]);
    $this->get('/')->assertSee('نص معتمد للاختبار')->assertSee('اسم معتمد للاختبار')->assertSee('ولي أمر');

    Livewire::test(EditTestimonial::class, ['record' => $record->id])
        ->fillForm(['is_published' => false])->call('save')->assertHasNoFormErrors();

    $this->assertDatabaseHas('testimonials', ['id' => $record->id, 'is_published' => false, 'is_approved' => true]);
    $this->get('/')->assertDontSee('نص معتمد للاختبار');
});

test('creating a published testimonial requires approval and publication consent', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateTestimonial::class)
        ->fillForm(['quote' => 'Unapproved text', 'person_name' => 'Test name', 'is_published' => true, 'is_approved' => false])
        ->call('create')->assertHasFormErrors(['is_approved' => 'accepted'])
        ->assertSee('يجب تأكيد صحة الرأي واعتماده وموافقة صاحبه قبل النشر.');

    $this->assertDatabaseCount('testimonials', 0);
});

test('existing testimonials cannot be published without approval and consent', function () {
    $this->actingAs(User::factory()->admin()->create());
    $record = Testimonial::factory()->create();

    Livewire::test(EditTestimonial::class, ['record' => $record->id])
        ->fillForm(['is_published' => true, 'is_approved' => false])
        ->call('save')->assertHasFormErrors(['is_approved' => 'accepted']);

    $this->assertDatabaseHas('testimonials', ['id' => $record->id, 'is_published' => false, 'is_approved' => false]);
});

test('withdrawing consent requires unpublishing and hides the testimonial', function () {
    $this->actingAs(User::factory()->admin()->create());
    $record = Testimonial::factory()->published()->create(['quote' => 'Withdrawn testimonial']);
    $component = Livewire::test(EditTestimonial::class, ['record' => $record->id]);

    $component->fillForm(['is_approved' => false])->call('save')->assertHasFormErrors(['is_approved' => 'accepted']);
    $this->assertDatabaseHas('testimonials', ['id' => $record->id, 'is_published' => true, 'is_approved' => true]);

    $component->fillForm(['is_published' => false, 'is_approved' => false])->call('save')->assertHasNoFormErrors();
    $this->assertDatabaseHas('testimonials', ['id' => $record->id, 'is_published' => false, 'is_approved' => false]);
    $this->get('/')->assertDontSee('Withdrawn testimonial');
});

test('testimonial form rejects missing text and public display name', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateTestimonial::class)->fillForm(['quote' => '', 'person_name' => ''])
        ->call('create')->assertHasFormErrors(['quote' => 'required', 'person_name' => 'required']);

    $this->assertDatabaseCount('testimonials', 0);
});

test('testimonial form validates text lengths and ordering before saving', function (string $field, mixed $value, string $rule) {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateTestimonial::class)
        ->fillForm(array_replace(['quote' => 'Test text', 'person_name' => 'Test name'], [$field => $value]))
        ->call('create')->assertHasFormErrors([$field => $rule]);

    $this->assertDatabaseCount('testimonials', 0);
})->with([
    'quote too long' => ['quote', str_repeat('a', 5001), 'max'],
    'name too long' => ['person_name', str_repeat('a', 256), 'max'],
    'attribution too long' => ['person_role', str_repeat('a', 256), 'max'],
    'missing order' => ['sort_order', null, 'required'],
    'negative order' => ['sort_order', -1, 'min'],
    'fractional order' => ['sort_order', 1.5, 'integer'],
    'overflow order' => ['sort_order', 4294967296, 'max'],
]);

test('testimonial table searches public text and sorts and filters statuses', function () {
    $this->actingAs(User::factory()->admin()->create());
    $later = Testimonial::factory()->published()->create(['person_name' => 'Z name', 'quote' => 'Unique quote', 'person_role' => 'Unique role', 'sort_order' => 2]);
    $first = Testimonial::factory()->create(['person_name' => 'A name', 'sort_order' => 1]);
    $tie = Testimonial::factory()->approved()->create(['person_name' => 'B name', 'sort_order' => 1]);

    $component = Livewire::test(ListTestimonials::class)
        ->assertCanSeeTableRecords([$first, $tie, $later], inOrder: true)
        ->sortTable('person_name', 'desc')->assertCanSeeTableRecords([$later, $tie, $first], inOrder: true);

    foreach (['Z name', 'Unique quote', 'Unique role'] as $search) {
        $component->searchTable($search)->assertCanSeeTableRecords([$later])->assertCanNotSeeTableRecords([$first, $tie]);
    }

    $component->searchTable('')->filterTable('is_published', true)
        ->assertCanSeeTableRecords([$later])->assertCanNotSeeTableRecords([$first, $tie])
        ->filterTable('is_published', false)->assertCanSeeTableRecords([$first, $tie])->assertCanNotSeeTableRecords([$later])
        ->filterTable('is_approved', true)->assertCanSeeTableRecords([$tie])->assertCanNotSeeTableRecords([$first]);
});

test('editing a legacy testimonial preserves its unused columns and other records', function () {
    $this->actingAs(User::factory()->admin()->create());
    $legacy = Testimonial::factory()->create(['avatar' => 'legacy/portrait.jpg', 'rating' => 2, 'person_name' => null]);
    $other = Testimonial::factory()->create();
    $original = $other->getRawOriginal();

    Livewire::test(EditTestimonial::class, ['record' => $legacy->id])
        ->fillForm(['quote' => 'Revised test text', 'person_name' => 'Approved test name'])
        ->call('save')->assertHasNoFormErrors();

    $this->assertDatabaseHas('testimonials', ['id' => $legacy->id, 'quote' => 'Revised test text', 'avatar' => 'legacy/portrait.jpg', 'rating' => 2, 'is_published' => false]);
    $this->assertDatabaseHas('testimonials', $original);
    $this->assertDatabaseCount('testimonials', 2);
});
