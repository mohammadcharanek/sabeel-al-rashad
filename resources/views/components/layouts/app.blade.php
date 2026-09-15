@props(['siteSettings', 'title' => null, 'description' => null, 'socialImage' => null])

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

    <x-site.header :site-settings="$siteSettings" />

    <main>
        {{ $slot }}
    </main>

    <x-site.footer :site-settings="$siteSettings" />

</body>
</html>
