@php
    $title = $homepageSettings->text('meta_title', $siteSettings->text('meta_title_ar', $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد')));
    $description = $homepageSettings->text('meta_description', $siteSettings->text('meta_description_ar', $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد').' - معا نبني جيلاً مبدعاً وواعياً'));
    $socialImage = $siteSettings->imageUrl('social_image') ?? $homepageSettings->imageUrl('hero_image_desktop', 'images/hero-desktop.jpg');
@endphp

<x-layouts.app :site-settings="$siteSettings" :title="$title" :description="$description" :social-image="$socialImage">
    @if ($homepageSettings->isSectionVisible('hero'))
        <x-home.hero :homepage-settings="$homepageSettings" :site-settings="$siteSettings" />
    @endif

    @if ($homepageSettings->isSectionVisible('intro'))
        <x-home.intro :homepage-settings="$homepageSettings" :site-settings="$siteSettings" />
    @endif

    @if ($homepageSettings->isSectionVisible('statistics'))
        <x-home.statistics />
    @endif

    @if ($homepageSettings->isSectionVisible('stages'))
        <x-home.stages :stages="$stages" />
    @endif

    @if ($homepageSettings->isSectionVisible('why-us'))
        <x-home.why-us :site-settings="$siteSettings" />
    @endif

    @if ($homepageSettings->isSectionVisible('school-life'))
        <x-home.school-life :site-settings="$siteSettings" />
    @endif

    @if ($homepageSettings->isSectionVisible('news'))
        <x-home.news :site-settings="$siteSettings" :news-posts="$newsPosts" />
    @endif

    @if ($homepageSettings->isSectionVisible('events'))
        <x-home.events :events="$events" />
    @endif

    @if ($homepageSettings->isSectionVisible('principal-message'))
        <x-home.principal-message :homepage-settings="$homepageSettings" :site-settings="$siteSettings" />
    @endif

    @if ($homepageSettings->isSectionVisible('testimonials'))
        <x-home.testimonials />
    @endif

    @if ($homepageSettings->isSectionVisible('admissions-cta'))
        <x-home.admissions-cta :homepage-settings="$homepageSettings" :site-settings="$siteSettings" />
    @endif
</x-layouts.app>
