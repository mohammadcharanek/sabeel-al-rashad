@props(['siteSettings', 'homepageSettings'])

<section
    id="home"
    class="relative isolate overflow-hidden
           bg-brand-navy-dark
           min-h-[560px]
           md:min-h-[620px]
           lg:min-h-[670px]"
>

    {{-- Background image --}}
    @if ($heroImage = $homepageSettings->imageUrl('hero_image_desktop', 'images/hero-desktop.jpg'))
        <picture>
            @if ($mobileImage = $homepageSettings->imageUrl('hero_image_mobile'))
                <source media="(max-width: 767px)" srcset="{{ $mobileImage }}">
            @endif
            <img
                src="{{ $heroImage }}"
                alt="طلاب {{ $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد') }} يحملون العلم اللبناني"
                fetchpriority="high"
                width="1657"
                height="926"
                class="absolute inset-0 -z-20
                       h-full w-full object-cover
                       object-center"
            >

        </picture>
    @endif

    {{-- Overlay --}}
    <div
        class="absolute inset-0 -z-10
               bg-gradient-to-r
               from-brand-navy-dark/72
               via-brand-navy-dark/28
               to-transparent"
        aria-hidden="true"
    ></div>

    {{-- Bottom fade --}}
    <div
        class="absolute inset-x-0 bottom-0 -z-10
               h-[220px]
               bg-gradient-to-t
               from-brand-navy-dark/85
               via-brand-navy-dark/35
               to-transparent"
        aria-hidden="true"
    ></div>

    <div
        class="mx-auto flex min-h-[560px]
               max-w-[1200px] items-center
               px-4 pt-16 pb-36 sm:pb-16
               md:min-h-[620px] md:px-8
               lg:min-h-[670px]"
    >

        <div
            class="max-w-[620px]
                   text-right text-white"
        >

            <div
                class="inline-flex items-center gap-2
                       rounded-full border border-brand-gold/30
                       bg-brand-navy-dark/45
                       px-4 py-2
                       text-[13px] font-semibold
                       text-brand-gold
                       backdrop-blur"
            >
                {{ $homepageSettings->text('hero_eyebrow', 'تعليم يفتح آفاقاً أوسع') }}
            </div>

            <h1
                class="mt-5 text-[34px] font-black
                       leading-[1.35]
                       sm:text-[42px]
                       md:text-[52px]
                       lg:text-[58px]"
            >
                @if (filled($homepageSettings->hero_title))
                    {{ $homepageSettings->hero_title }}
                @else
                    نصنع جيلاً واعياً
                    <span class="block text-brand-gold">
                        ومستعداً للمستقبل
                    </span>
                @endif
            </h1>

            <p
                class="mt-5 max-w-[540px]
                       text-[15px] leading-8
                       text-white/85
                       sm:text-[17px]"
            >
                {{ $homepageSettings->text('hero_description', 'بيئة تعليمية متوازنة تجمع بين المعرفة والقيم، وتدعم الطالب في بناء شخصيته ومهاراته والاستعداد لمستقبل واعد.') }}
            </p>

            <div
                dir="rtl"
                class="mt-8 flex flex-wrap gap-3"
            >

                <a
                    href="{{ $homepageSettings->linkUrl('hero_primary_url', '#admissions') }}"
                    class="inline-flex h-[48px]
                           items-center justify-center
                           rounded-lg bg-brand-gold
                           px-7 text-[16px] font-bold
                           text-brand-navy-dark
                           transition
                           hover:bg-white"
                >
                    {{ $homepageSettings->text('hero_primary_label', 'سجّل الآن') }}
                </a>

                <a
                    href="{{ $homepageSettings->linkUrl('hero_secondary_url', '#about') }}"
                    class="inline-flex h-[48px]
                           items-center justify-center
                           rounded-lg border border-white/35
                           bg-white/5 px-7
                           text-[16px] font-semibold
                           text-white backdrop-blur
                           transition
                           hover:border-white
                           hover:bg-white
                           hover:text-brand-navy-dark"
                >
                    {{ $homepageSettings->text('hero_secondary_label', 'اكتشف مدرستنا') }}
                </a>

            </div>

        </div>

    </div>

    {{-- Trust strip --}}
    <div
        class="absolute inset-x-0 bottom-0
               border-t border-white/10
               bg-brand-navy-dark/78
               backdrop-blur-md"
    >
        <div
            class="mx-auto grid max-w-[1200px]
                   grid-cols-1 gap-3
                   px-4 py-4 text-center
                   text-[13px] font-semibold
                   text-white/85
                   sm:grid-cols-3
                   md:px-8"
        >
            <span>تعليم متميز</span>
            <span>قيم راسخة</span>
            <span>استعداد للمستقبل</span>
        </div>
    </div>

</section>
