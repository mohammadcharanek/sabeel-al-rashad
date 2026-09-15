<?php

use App\Filament\Pages\HomepageSettings;
use App\Models\HomepageSetting;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('homepage renders the original Arabic content without creating settings', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('ثانوية سبيل الرشاد')
        ->assertSee('نصنع جيلاً واعياً')
        ->assertSee('أ. إبراهيم العزنكي')
        ->assertSee('info@sabeelalrashad.com')
        ->assertSee('images/school-logo.jpg')
        ->assertSee('images/principal.jpg')
        ->assertSee('<html lang="ar" dir="rtl">', false)
        ->assertSee('data-header-menu', false)
        ->assertSee('id="compact-navigation"', false)
        ->assertDontSee('aria-label="Facebook"', false)
        ->assertDontSee('aria-label="Instagram"', false)
        ->assertDontSee('aria-label="YouTube"', false)
        ->assertDontSee('aria-label="WhatsApp"', false);

    $this->assertDatabaseCount('site_settings', 0);
    $this->assertDatabaseCount('homepage_settings', 0);
});

test('homepage uses the configured Arabic identity contacts and principal instead of legacy homepage fields', function () {
    SiteSetting::factory()->create([
        'school_name_ar' => 'مدرسة الأمل',
        'short_description_ar' => 'معرفة وقيم للمستقبل',
        'primary_phone' => '+96181234567',
        'secondary_phone' => '+96171234567',
        'contact_email' => 'contact@example.test',
        'address_ar' => 'بيروت، لبنان',
        'principal_name_ar' => 'الدكتورة مريم',
        'principal_title_ar' => 'مديرة المدرسة',
        'principal_message_ar' => 'نرحب بطلابنا في عام جديد',
        'facebook_url' => 'https://facebook.com/example-school',
        'instagram_url' => 'https://instagram.com/example-school',
        'youtube_url' => 'https://youtube.com/@example-school',
        'notification_email' => 'private@example.test',
    ]);
    HomepageSetting::query()->create([
        'principal_name' => 'اسم قديم',
        'principal_message' => 'رسالة قديمة',
        'principal_eyebrow' => 'رسالة الإدارة',
    ]);

    $this->get('/')
        ->assertSee('مدرسة الأمل')
        ->assertSee('لماذا مدرسة الأمل؟')
        ->assertSee('الحياة في مدرسة الأمل')
        ->assertSee('انضم إلى عائلة مدرسة الأمل')
        ->assertSee('معرفة وقيم للمستقبل')
        ->assertSee('href="tel:+96181234567"', false)
        ->assertSee('href="tel:+96171234567"', false)
        ->assertSee('href="mailto:contact@example.test"', false)
        ->assertSee('بيروت، لبنان')
        ->assertSee('الدكتورة مريم')
        ->assertSee('مديرة المدرسة')
        ->assertSee('نرحب بطلابنا في عام جديد')
        ->assertSee('رسالة الإدارة')
        ->assertSee('href="https://facebook.com/example-school"', false)
        ->assertSee('href="https://instagram.com/example-school"', false)
        ->assertSee('href="https://youtube.com/@example-school"', false)
        ->assertDontSee('اسم قديم')
        ->assertDontSee('رسالة قديمة')
        ->assertDontSee('private@example.test');
});

test('homepage renders configured hero introduction and admissions fields', function () {
    HomepageSetting::query()->create([
        'hero_eyebrow' => 'التمهيد الرئيسي',
        'hero_title' => 'عنوان الواجهة',
        'hero_description' => 'وصف الواجهة',
        'hero_primary_label' => 'ابدأ معنا',
        'hero_primary_url' => 'https://example.test/register',
        'hero_secondary_label' => 'المزيد عنا',
        'hero_secondary_url' => 'https://example.test/about',
        'intro_eyebrow' => 'تعريف المدرسة',
        'intro_title' => 'عنوان التعريف',
        'intro_paragraph_one' => 'فقرة التعريف الأولى',
        'intro_paragraph_two' => 'فقرة التعريف الثانية',
        'experience_label' => 'سنوات من العطاء',
        'admissions_eyebrow' => 'التسجيل المدرسي',
        'admissions_title' => 'انضم إلينا',
        'admissions_description' => 'باب التسجيل مفتوح',
        'admissions_primary_label' => 'طلب التسجيل',
        'admissions_primary_url' => 'https://example.test/apply',
        'admissions_secondary_label' => 'استفسر الآن',
        'admissions_secondary_url' => 'https://example.test/contact',
    ]);

    $response = $this->get('/');

    foreach ([
        'التمهيد الرئيسي', 'عنوان الواجهة', 'وصف الواجهة', 'ابدأ معنا', 'المزيد عنا',
        'تعريف المدرسة', 'عنوان التعريف', 'فقرة التعريف الأولى', 'فقرة التعريف الثانية',
        'سنوات من العطاء', 'التسجيل المدرسي', 'انضم إلينا', 'باب التسجيل مفتوح',
        'طلب التسجيل', 'استفسر الآن',
    ] as $text) {
        $response->assertSee($text);
    }

    foreach (['register', 'about', 'apply', 'contact'] as $path) {
        $response->assertSee('href="https://example.test/'.$path.'"', false);
    }

    $response->assertSee('ثانوية سبيل الرشاد');
    $this->assertDatabaseCount('site_settings', 0);
});

test('blank homepage fields retain the existing content', function (?string $value) {
    HomepageSetting::query()->create([
        'hero_title' => $value,
        'hero_description' => $value,
        'intro_title' => $value,
        'admissions_title' => $value,
        'hero_primary_url' => $value,
    ]);

    $this->get('/')
        ->assertSee('نصنع جيلاً واعياً')
        ->assertSee('بيئة تعليمية متوازنة')
        ->assertSee('مرحباً بكم في')
        ->assertSee('ابدأ رحلة طفلك التعليمية معنا')
        ->assertSee('href="#admissions"', false);
})->with([null, '', '   ']);

test('only the explicitly disabled homepage section is hidden', function (string $section, string $id) {
    HomepageSetting::query()->create(['section_visibility' => [$section => false]]);

    $marker = $section === 'statistics' ? 'aria-label="إحصائيات المدرسة"' : 'id="'.$id.'"';
    $response = $this->get('/')->assertDontSee($marker, false);

    foreach (['home', 'about', 'statistics', 'stages', 'why-us', 'school-life', 'news', 'events', 'principal-message', 'testimonials', 'admissions'] as $visibleId) {
        if ($visibleId !== $id) {
            $response->assertSee($visibleId === 'statistics' ? 'aria-label="إحصائيات المدرسة"' : 'id="'.$visibleId.'"', false);
        }
    }
})->with([
    'hero' => ['hero', 'home'],
    'intro' => ['intro', 'about'],
    'statistics' => ['statistics', 'statistics'],
    'stages' => ['stages', 'stages'],
    'why us' => ['why-us', 'why-us'],
    'school life' => ['school-life', 'school-life'],
    'news' => ['news', 'news'],
    'events' => ['events', 'events'],
    'principal' => ['principal-message', 'principal-message'],
    'testimonials' => ['testimonials', 'testimonials'],
    'admissions' => ['admissions-cta', 'admissions'],
]);

test('missing or null visibility remains visible when settings exist', function () {
    HomepageSetting::query()->create(['section_visibility' => ['hero' => null, 'news' => true]]);

    $this->get('/')
        ->assertSee('id="home"', false)
        ->assertSee('id="about"', false)
        ->assertSee('id="news"', false);
});

test('SEO uses homepage overrides then global defaults then existing fallback', function (?string $homeTitle, ?string $homeDescription, ?string $globalTitle, ?string $globalDescription, string $title, string $description) {
    SiteSetting::factory()->create(['meta_title_ar' => $globalTitle, 'meta_description_ar' => $globalDescription]);
    HomepageSetting::query()->create(['meta_title' => $homeTitle, 'meta_description' => $homeDescription]);

    $this->get('/')
        ->assertSee('<title>'.$title.'</title>', false)
        ->assertSee('name="description"'."\n".'        content="'.$description.'"', false)
        ->assertSee('<meta property="og:title" content="'.$title.'">', false)
        ->assertSee('<meta property="og:description" content="'.$description.'">', false);
})->with([
    'homepage overrides' => ['عنوان الصفحة', 'وصف الصفحة', 'عنوان الموقع', 'وصف الموقع', 'عنوان الصفحة', 'وصف الصفحة'],
    'global defaults' => [null, null, 'عنوان الموقع', 'وصف الموقع', 'عنوان الموقع', 'وصف الموقع'],
    'independent overrides' => ['عنوان الصفحة', null, 'عنوان الموقع', 'وصف الموقع', 'عنوان الصفحة', 'وصف الموقع'],
    'blank overrides' => [' ', '', 'عنوان الموقع', 'وصف الموقع', 'عنوان الموقع', 'وصف الموقع'],
    'existing fallback' => [null, null, null, null, 'ثانوية سبيل الرشاد', 'ثانوية سبيل الرشاد - معا نبني جيلاً مبدعاً وواعياً'],
]);

test('settings images use the public disk including mobile hero and social image', function () {
    Storage::fake('public', ['url' => 'https://media.example.test']);
    foreach (['logo', 'principal', 'social', 'favicon', 'desktop', 'mobile', 'intro'] as $name) {
        Storage::disk('public')->put("settings/{$name}.jpg", 'test image');
    }
    SiteSetting::factory()->create([
        'logo' => 'settings/logo.jpg',
        'principal_photo' => 'settings/principal.jpg',
        'social_image' => 'settings/social.jpg',
        'favicon' => 'settings/favicon.jpg',
    ]);
    HomepageSetting::query()->create([
        'hero_image_desktop' => 'settings/desktop.jpg',
        'hero_image_mobile' => 'settings/mobile.jpg',
        'intro_image' => 'settings/intro.jpg',
    ]);

    $response = $this->get('/');

    foreach (['logo', 'principal', 'desktop', 'intro'] as $name) {
        $response->assertSee('src="https://media.example.test/settings/'.$name.'.jpg"', false);
    }

    $response->assertSee('srcset="https://media.example.test/settings/mobile.jpg"', false)
        ->assertSee('<meta property="og:image" content="https://media.example.test/settings/social.jpg">', false)
        ->assertSee('<link rel="icon" href="https://media.example.test/settings/favicon.jpg">', false)
        ->assertDontSee('src="http://127.0.0.1:8000/images/principal.jpg"', false);
    Storage::disk('public')->assertExists('settings/principal.jpg');
});

test('missing or unsafe managed images retain static fallbacks', function (?string $path) {
    Storage::fake('public');
    Storage::disk('public')->put('settings/logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
    Storage::disk('public')->put('private.jpg', 'test image');
    SiteSetting::factory()->create(['logo' => $path, 'principal_photo' => $path, 'social_image' => $path]);
    HomepageSetting::query()->create(['hero_image_desktop' => $path, 'hero_image_mobile' => $path, 'intro_image' => $path]);

    $this->get('/')
        ->assertOk()
        ->assertSee('images/school-logo.jpg')
        ->assertSee('images/principal.jpg')
        ->assertSee('images/hero-desktop.jpg')
        ->assertSee('images/school-building.jpg')
        ->assertDontSee('<source ', false)
        ->assertDontSee('src=""', false);
})->with([
    'empty' => null,
    'missing' => 'settings/missing.jpg',
    'traversal' => '../private.jpg',
    'windows traversal' => '..\private.jpg',
    'absolute path' => '/private.jpg',
    'external URL' => 'https://example.test/photo.jpg',
    'encoded traversal' => '%2e%2e/private.jpg',
    'active format' => 'settings/logo.svg',
]);

test('the homepage escapes settings text and refuses unsafe configured links', function () {
    $payload = '<script>alert("settings")</script>';
    SiteSetting::factory()->create([
        'school_name_ar' => $payload,
        'short_description_ar' => $payload,
        'principal_name_ar' => $payload,
        'principal_message_ar' => $payload,
        'principal_title_ar' => $payload,
        'address_ar' => $payload,
        'meta_title_ar' => $payload,
        'meta_description_ar' => $payload,
        'facebook_url' => 'javascript:alert(1)',
        'instagram_url' => 'data:text/html,unsafe',
        'youtube_url' => '//example.test/unsafe',
    ]);
    HomepageSetting::query()->create([
        'hero_title' => $payload,
        'hero_description' => $payload,
        'intro_title' => $payload,
        'intro_paragraph_one' => $payload,
        'intro_paragraph_two' => $payload,
        'admissions_title' => $payload,
        'admissions_description' => $payload,
        'hero_primary_url' => 'javascript:alert(1)',
        'hero_secondary_url' => 'data:text/html,unsafe',
        'admissions_primary_url' => '//example.test/unsafe',
        'admissions_secondary_url' => 'ftp://example.test/unsafe',
    ]);

    $this->get('/')
        ->assertSee($payload)
        ->assertDontSee($payload, false)
        ->assertSee('<title>'.e($payload).'</title>', false)
        ->assertSee('<meta property="og:description" content="'.e($payload).'">', false)
        ->assertSee('href="#admissions"', false)
        ->assertSee('href="#about"', false)
        ->assertSee('href="#contact"', false)
        ->assertDontSee('href="javascript:', false)
        ->assertDontSee('href="data:', false)
        ->assertDontSee('href="//example', false)
        ->assertDontSee('href="ftp:', false)
        ->assertDontSee('aria-label="Facebook"', false)
        ->assertDontSee('aria-label="Instagram"', false)
        ->assertDontSee('aria-label="YouTube"', false);
});

test('settings are read once each per homepage request and updates appear on the next request', function () {
    $site = SiteSetting::factory()->create(['id' => 42, 'school_name_ar' => 'الاسم الأول']);
    $homepage = HomepageSetting::query()->create(['hero_title' => 'العنوان الأول']);
    DB::enableQueryLog();
    DB::flushQueryLog();

    $this->get('/')->assertSee('الاسم الأول')->assertSee('العنوان الأول');

    $queries = collect(DB::getQueryLog())->pluck('query');
    DB::disableQueryLog();
    expect($queries->filter(fn (string $sql): bool => str_contains($sql, 'from "site_settings"')))->toHaveCount(1);
    expect($queries->filter(fn (string $sql): bool => str_contains($sql, 'from "homepage_settings"')))->toHaveCount(1);

    $site->update(['school_name_ar' => 'الاسم الجديد']);
    $homepage->update(['hero_title' => 'العنوان الجديد']);

    $this->get('/')
        ->assertSee('الاسم الجديد')
        ->assertSee('العنوان الجديد')
        ->assertDontSee('الاسم الأول')
        ->assertDontSee('العنوان الأول');
});

test('saving the homepage editor preserves visible defaults and legacy principal data', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(User::factory()->admin()->create());
    HomepageSetting::query()->create([
        'section_visibility' => ['news' => false, 'hero' => null],
        'principal_name' => 'بيانات محفوظة',
    ]);

    Livewire::test(HomepageSettings::class)
        ->assertFormSet(['section_visibility.hero' => true, 'section_visibility.intro' => true, 'section_visibility.news' => false])
        ->fillForm(['hero_title' => 'عنوان بعد الحفظ'])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->get('/')
        ->assertSee('عنوان بعد الحفظ')
        ->assertSee('id="about"', false)
        ->assertDontSee('id="news"', false);
    $this->assertDatabaseHas('homepage_settings', ['principal_name' => 'بيانات محفوظة']);
});
