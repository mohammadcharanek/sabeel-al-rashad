@props(['siteSettings', 'homepageSettings'])

@php
    $navigationLinks = $homepageSettings->navigationLinks();
@endphp

<header
    data-site-header
    class="sticky top-0 z-50 border-b border-white/10 bg-brand-navy-dark/95 backdrop-blur-xl"
>
    <div
        dir="ltr"
        class="mx-auto flex min-h-[72px] w-full max-w-[1440px] items-center justify-between gap-3 px-4 py-3 sm:px-6 md:px-8 xl:min-h-[78px]"
    >
        <a href="{{ route('home') }}" class="flex min-w-0 flex-row-reverse items-center gap-2 md:flex-row">
            @if ($logoUrl = $siteSettings->imageUrl('logo', 'images/school-logo.jpg'))
                <img
                    src="{{ $logoUrl }}"
                    alt="شعار {{ $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد') }}"
                    width="48"
                    height="48"
                    class="h-11 w-11 shrink-0 rounded-full object-cover ring-2 ring-brand-gold-dark/35 xl:h-12 xl:w-12"
                >
            @endif
            <div class="min-w-0 text-right">
                <div dir="rtl" class="text-[14px] font-black leading-relaxed text-white sm:text-[16px]">
                    {{ $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد') }}
                </div>
                <div dir="ltr" class="mt-0.5 text-[9px] font-medium text-brand-gold sm:text-[10px]">
                    Sabeel Al Rashad Secondary School
                </div>
            </div>
        </a>

        <nav class="hidden items-center gap-4 whitespace-nowrap text-[13px] font-bold text-[#EBF0F7] xl:flex" aria-label="التنقل الرئيسي">
            @foreach ($navigationLinks as $link)
                <a
                    href="{{ $link['href'] }}"
                    data-nav-link
                    class="inline-flex min-h-11 items-center border-b-2 border-transparent py-2 transition hover:text-brand-gold aria-[current=location]:border-brand-gold aria-[current=location]:text-brand-gold"
                >{{ $link['label'] }}</a>
            @endforeach
        </nav>

        <div class="hidden shrink-0 items-center gap-3 xl:flex">
            <span class="text-xs text-white/70" lang="ar">العربية</span>
            @if ($admissionsUrl = $homepageSettings->sectionUrl('admissions-cta'))
                <a href="{{ $admissionsUrl }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand-gold px-5 py-2 text-sm font-bold text-brand-navy-dark transition hover:bg-white">
                    سجّل الآن
                </a>
            @endif
        </div>

        <div class="flex shrink-0 items-center xl:hidden">
            <details class="relative" data-header-menu>
                <summary
                    class="flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-lg border border-white/10 text-white transition hover:bg-white/5 [&::-webkit-details-marker]:hidden"
                    aria-label="القائمة الرئيسية"
                    aria-controls="compact-navigation"
                >
                    <img src="{{ asset('images/menu.svg') }}" alt="" width="22" height="22" class="h-[22px] w-[22px]">
                </summary>
                <nav
                    id="compact-navigation"
                    dir="rtl"
                    aria-label="التنقل الرئيسي للجوال"
                    class="absolute right-0 top-[52px] max-h-[calc(100dvh-100px)] w-[min(280px,calc(100vw-2rem))] overflow-y-auto rounded-xl border border-white/10 bg-brand-navy-dark shadow-2xl"
                >
                    <div class="flex flex-col p-2 text-right text-white">
                        @foreach ($navigationLinks as $link)
                            <a href="{{ $link['href'] }}" data-nav-link class="rounded-lg px-4 py-3 hover:bg-white/5 aria-[current=location]:bg-white/10 aria-[current=location]:text-brand-gold">
                                {{ $link['label'] }}
                            </a>
                        @endforeach
                        <span class="border-t border-white/10 px-4 py-3 text-xs text-white/60" lang="ar">العربية</span>
                    </div>
                </nav>
            </details>
        </div>
    </div>
</header>
