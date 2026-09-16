<?php

namespace App\Models;

class HomepageSetting extends SingletonSetting
{
    public const SECTIONS = [
        'hero', 'intro', 'statistics', 'stages', 'why-us', 'school-life',
        'news', 'events', 'principal-message', 'testimonials', 'admissions-cta',
    ];

    protected $fillable = [
        'singleton_key',
        'hero_eyebrow',
        'hero_title',
        'hero_description',
        'hero_image_desktop',
        'hero_image_mobile',
        'hero_primary_label',
        'hero_primary_url',
        'hero_secondary_label',
        'hero_secondary_url',
        'intro_eyebrow',
        'intro_title',
        'intro_paragraph_one',
        'intro_paragraph_two',
        'intro_image',
        'experience_label',
        'principal_eyebrow',
        'principal_message',
        'principal_name',
        'principal_title',
        'principal_image',
        'admissions_eyebrow',
        'admissions_title',
        'admissions_description',
        'admissions_primary_label',
        'admissions_primary_url',
        'admissions_secondary_label',
        'admissions_secondary_url',
        'meta_title',
        'meta_description',
        'section_visibility',
    ];

    protected $attributes = [
        'singleton_key' => 'global',
    ];

    protected function casts(): array
    {
        return [
            'section_visibility' => 'array',
        ];
    }

    public function isSectionVisible(string $section): bool
    {
        return data_get($this->section_visibility, $section, true) !== false;
    }

    public function sectionUrl(string $section): ?string
    {
        if (! in_array($section, self::SECTIONS, true) || ! $this->isSectionVisible($section)) {
            return null;
        }

        return '#'.match ($section) {
            'hero' => 'home',
            'intro' => 'about',
            'admissions-cta' => 'admissions',
            default => $section,
        };
    }

    public function linkUrl(string $attribute, ?string $fallback = null): ?string
    {
        $url = parent::linkUrl($attribute);

        if ($url === null) {
            return $fallback;
        }

        $parts = parse_url($url);
        $home = parse_url(route('home'));

        if (isset($parts['fragment'])
            && strtolower($parts['host']) === strtolower($home['host'])
            && ($parts['port'] ?? null) === ($home['port'] ?? null)
            && rtrim($parts['path'] ?? '', '/') === rtrim($home['path'] ?? '', '/')) {
            $anchors = ['#contact', '#main-content'];

            foreach (self::SECTIONS as $section) {
                if ($anchor = $this->sectionUrl($section)) {
                    $anchors[] = $anchor;
                }
            }

            if (! in_array('#'.rawurldecode($parts['fragment']), $anchors, true)) {
                return $fallback;
            }
        }

        return $url;
    }

    /** @return list<array{label: string, href: string}> */
    public function navigationLinks(): array
    {
        $links = [];

        foreach ([
            'hero' => 'الرئيسية',
            'intro' => 'عن المدرسة',
            'stages' => 'المراحل التعليمية',
            'admissions-cta' => 'القبول والتسجيل',
            'school-life' => 'الحياة المدرسية',
            'news' => 'الأخبار',
        ] as $section => $label) {
            if ($href = $this->sectionUrl($section)) {
                $links[] = ['label' => $label, 'href' => $href];
            }
        }

        $links[] = ['label' => 'تواصل معنا', 'href' => '#contact'];

        return $links;
    }
}
