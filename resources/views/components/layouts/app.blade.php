@props(['siteSettings', 'homepageSettings', 'title' => null, 'description' => null, 'socialImage' => null])

@php
    $title ??= $siteSettings->text('meta_title_ar', $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد'));
    $description ??= $siteSettings->text('meta_description_ar', $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد').' - معا نبني جيلاً مبدعاً وواعياً');
    $socialImage ??= $siteSettings->imageUrl('social_image');
@endphp


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>{{ $title }}</title>

    <meta
        name="description"
        content="{{ $description }}"
    >

    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    @if ($socialImage)
        <meta property="og:image" content="{{ $socialImage }}">
    @endif
    @if ($favicon = $siteSettings->imageUrl('favicon'))
        <link rel="icon" href="{{ $favicon }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-white font-arabic text-text-primary antialiased">

    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-white focus:p-4 focus:text-brand-navy">انتقل إلى المحتوى</a>

    <x-site.header :site-settings="$siteSettings" :homepage-settings="$homepageSettings" />

    <main id="main-content" tabindex="-1">
        {{ $slot }}
    </main>

    <x-site.footer :site-settings="$siteSettings" :homepage-settings="$homepageSettings" />

</body>
</html>
