<?php

use App\Models\HomepageSetting;
use App\Models\SiteSetting;

test('disabled sections have no navigation or fallback action pointing to them', function (string $section, string $anchor) {
    HomepageSetting::query()->create(['section_visibility' => [$section => false]]);

    $this->get('/')
        ->assertOk()
        ->assertDontSee('href="#'.$anchor.'"', false)
        ->assertDontSee('id="'.$anchor.'"', false)
        ->assertSee('href="#contact"', false);
})->with([
    'hero' => ['hero', 'home'],
    'introduction' => ['intro', 'about'],
    'stages' => ['stages', 'stages'],
    'admissions' => ['admissions-cta', 'admissions'],
    'school life' => ['school-life', 'school-life'],
    'news' => ['news', 'news'],
]);

test('default desktop mobile and footer navigation all reach existing sections', function () {
    $html = $this->get('/')->assertOk()->getContent();
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
    $xpath = new DOMXPath($document);

    foreach (['home', 'about', 'stages', 'admissions', 'school-life', 'news', 'contact'] as $anchor) {
        expect($xpath->query('//nav//a[@href="#'.$anchor.'"]')->length)->toBe(3);
        expect($xpath->query('//*[@id="'.$anchor.'"]')->length)->toBe(1);
    }
});

test('contact remains reachable when every optional homepage section is hidden', function () {
    HomepageSetting::query()->create(['section_visibility' => array_fill_keys(HomepageSetting::SECTIONS, false)]);

    $html = $this->get('/')->assertOk()->getContent();
    preg_match_all('/href="#([^"]*)"/', $html, $matches);

    expect(array_values(array_unique($matches[1])))->toBe(['main-content', 'contact']);
});

test('configured external hero actions remain usable when their default sections are hidden', function () {
    HomepageSetting::query()->create([
        'section_visibility' => ['intro' => false, 'admissions-cta' => false],
        'hero_primary_url' => 'https://example.test/apply',
        'hero_secondary_url' => 'https://example.test/about',
    ]);

    $this->get('/')
        ->assertSee('href="https://example.test/apply"', false)
        ->assertSee('href="https://example.test/about"', false)
        ->assertDontSee('href="#admissions"', false)
        ->assertDontSee('href="#about"', false);
});

test('configured links to missing homepage anchors use a working fallback', function (string $anchor) {
    HomepageSetting::query()->create([
        'section_visibility' => ['news' => false],
        'hero_primary_url' => route('home').'#'.$anchor,
        'admissions_primary_url' => route('home').'#'.$anchor,
    ]);

    $this->get('/')
        ->assertDontSee('href="'.route('home').'#'.$anchor.'"', false)
        ->assertSee('href="#admissions"', false)
        ->assertSee('href="#contact"', false);
})->with(['news', 'missing-section', '']);

test('configured links to visible homepage anchors are preserved', function () {
    HomepageSetting::query()->create(['hero_primary_url' => route('home').'#news']);

    $this->get('/')->assertSee('href="'.route('home').'#news"', false);
});

test('WhatsApp uses a normalized international number and safely encoded Arabic message', function (string $number) {
    SiteSetting::factory()->create([
        'whatsapp_enabled' => true,
        'whatsapp_number' => $number,
        'whatsapp_message_ar' => 'مرحبا & التسجيل؟',
    ]);

    $html = $this->get('/')
        ->assertSee('href="https://wa.me/96170123456?text=%D9%85%D8%B1%D8%AD%D8%A8%D8%A7%20%26%20%D8%A7%D9%84%D8%AA%D8%B3%D8%AC%D9%8A%D9%84%D8%9F"', false)
        ->assertSee('aria-label="تواصل مع المدرسة عبر واتساب (يفتح في نافذة جديدة)"', false)
        ->getContent();

    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
    $xpath = new DOMXPath($document);
    expect($xpath->query('//a[starts-with(@href,"https://wa.me/") and @target="_blank" and @rel="noopener noreferrer"]')->length)->toBe(2);
})->with(['+96170123456', '00961 70 123 456', '+961 (70) 123-456', '96170123456']);

test('WhatsApp omits a blank message and remains available with admissions hidden', function () {
    SiteSetting::factory()->create(['whatsapp_enabled' => true, 'whatsapp_number' => '+96170123456', 'whatsapp_message_ar' => '   ']);
    HomepageSetting::query()->create(['section_visibility' => ['admissions-cta' => false]]);

    $this->get('/')
        ->assertSee('href="https://wa.me/96170123456"', false)
        ->assertDontSee('https://wa.me/96170123456?text=', false);
});

test('WhatsApp is hidden when disabled or its number is invalid', function (bool $enabled, ?string $number) {
    SiteSetting::factory()->create(['whatsapp_enabled' => $enabled, 'whatsapp_number' => $number]);

    $this->get('/')->assertDontSee('https://wa.me/', false);
})->with([
    'disabled' => [false, '+96170123456'],
    'missing' => [true, null],
    'blank' => [true, '  '],
    'local number' => [true, '070123456'],
    'too short' => [true, '+123'],
    'too long' => [true, '+1234567890123456'],
    'letters' => [true, '+961ABC123456'],
    'URL' => [true, 'javascript:alert(1)'],
    'extension' => [true, '+96170123456#123'],
    'duplicate prefix' => [true, '++96170123456'],
]);

test('unfinished controls cannot be mistaken for functional links or forms', function () {
    $html = $this->get('/')
        ->assertDontSee('href="#"', false)
        ->assertDontSee('href=""', false)
        ->assertDontSee('AR | EN')
        ->assertSee('الاشتراك في النشرة الإخبارية غير متاح حالياً.')
        ->assertSee('ستُعرض صور الحياة المدرسية هنا بعد اعتمادها ونشرها.')
        ->getContent();

    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
    $xpath = new DOMXPath($document);
    expect($xpath->query('//fieldset[@disabled]//input[@type="email" and @aria-label]')->length)->toBe(1);
    expect($xpath->query('//fieldset[@disabled]//button')->length)->toBe(1);
});
