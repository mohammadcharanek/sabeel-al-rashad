@props(['siteSettings', 'homepageSettings'])

<section id="home" class="overflow-hidden bg-brand-navy-dark text-white">
    <div class="mx-auto grid max-w-[1440px] items-center gap-8 px-4 py-10 sm:px-6 md:gap-10 md:px-8 md:py-12 lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)] lg:gap-12 lg:py-16">
        <div class="min-w-0 text-right">
            <span class="inline-flex rounded-full border border-brand-gold/40 px-4 py-2 text-[13px] font-semibold text-brand-gold">
                {{ $homepageSettings->text('hero_eyebrow', 'تعليم يفتح آفاقاً أوسع') }}
            </span>

            <h1 class="mt-5 text-[32px] font-black leading-[1.6] sm:text-[40px] xl:text-[46px]">
                @if (filled($homepageSettings->hero_title))
                    {{ $homepageSettings->hero_title }}
                @else
                    معًا نبني جيلًا
                    <span class="block text-brand-gold">مبدعًا وواعيًا</span>
                @endif
            </h1>

            <p class="mt-5 max-w-[540px] text-[15px] leading-8 text-white/85 sm:text-[17px]">
                {{ $homepageSettings->text('hero_description', 'بيئة تعليمية متوازنة تجمع بين المعرفة والقيم، وتدعم الطالب في بناء شخصيته ومهاراته والاستعداد لمستقبل واعد.') }}
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                @if ($primaryUrl = $homepageSettings->linkUrl('hero_primary_url', $homepageSettings->sectionUrl('admissions-cta')))
                    <a href="{{ $primaryUrl }}" class="inline-flex min-h-12 items-center justify-center rounded-lg bg-brand-gold px-6 py-3 text-base font-bold text-brand-navy-dark transition hover:bg-white">
                        {{ $homepageSettings->text('hero_primary_label', 'سجّل الآن') }}
                    </a>
                @endif
                @if ($secondaryUrl = $homepageSettings->linkUrl('hero_secondary_url', $homepageSettings->sectionUrl('intro')))
                    <a href="{{ $secondaryUrl }}" class="inline-flex min-h-12 items-center justify-center rounded-lg border border-white/50 px-6 py-3 text-base font-semibold text-white transition hover:bg-white hover:text-brand-navy-dark">
                        {{ $homepageSettings->text('hero_secondary_label', 'اكتشف مدرستنا') }}
                    </a>
                @endif
            </div>
        </div>

        @if ($heroImage = $homepageSettings->imageUrl('hero_image_desktop', 'images/hero-desktop.jpg'))
            <picture class="block min-w-0 overflow-hidden rounded-xl border border-white/15">
                @if ($mobileImage = $homepageSettings->imageUrl('hero_image_mobile'))
                    <source media="(max-width: 767px)" srcset="{{ $mobileImage }}">
                @endif
                <img
                    src="{{ $heroImage }}"
                    alt="طلاب {{ $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد') }} يحملون العلم اللبناني"
                    fetchpriority="high"
                    width="1657"
                    height="926"
                    class="block h-auto w-full"
                >
            </picture>
        @endif
    </div>

    <div class="border-t border-white/10 bg-brand-navy">
        <div class="mx-auto grid max-w-[1200px] grid-cols-1 gap-3 px-4 py-4 text-center text-[13px] font-semibold text-white/85 sm:grid-cols-3 md:px-8">
            <span>تعليم متميز</span>
            <span>قيم راسخة</span>
            <span>استعداد للمستقبل</span>
        </div>
    </div>
</section>
