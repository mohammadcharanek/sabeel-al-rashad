@props(['siteSettings'])

<header
    class="sticky top-0 z-50 border-b border-white/10
           bg-brand-navy-dark/95 backdrop-blur-xl"
>
    <div
        dir="ltr"
        class="mx-auto flex h-[72px] w-full max-w-[1320px]
               items-center justify-between px-4
               sm:px-6 md:px-8 xl:h-[78px]"
    >

        {{-- School identity --}}
        <a
            href="{{ route('home') }}"
            class="flex shrink-0 flex-row-reverse items-center gap-3
                   md:flex-row"
        >
            @if ($logoUrl = $siteSettings->imageUrl('logo', 'images/school-logo.jpg'))
                <img
                    src="{{ $logoUrl }}"
                    alt="شعار {{ $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد') }}"
                    class="h-11 w-11 shrink-0 rounded-full
                           object-cover ring-2 ring-brand-gold-dark/35
                           xl:h-12 xl:w-12"
                >
            @endif

            <div class="whitespace-nowrap text-right">
                <div
                    class="text-[15px] font-black leading-tight
                           text-white sm:text-[16px]"
                >
                    {{ $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد') }}
                </div>

                <div
                    dir="ltr"
                    class="mt-0.5 text-[10px] font-medium
                           tracking-[0.02em]
                           text-brand-gold-dark sm:text-[11px]"
                >
                    Sabeel Al Rashad Secondary School
                </div>
            </div>
        </a>

        {{-- Desktop navigation --}}
        <nav
            class="hidden items-center gap-7 whitespace-nowrap
                   text-[14px] font-bold text-[#EBF0F7]
                   xl:flex"
            aria-label="التنقل الرئيسي"
        >
            <a
                href="#home"
                class="relative py-2 transition
                       hover:text-brand-gold
                       after:absolute after:inset-x-0 after:-bottom-0.5
                       after:h-[2px] after:scale-x-0 after:bg-brand-gold
                       after:transition-transform hover:after:scale-x-100"
            >
                الرئيسية
            </a>

            <a
                href="#about"
                class="relative py-2 transition
                       hover:text-brand-gold
                       after:absolute after:inset-x-0 after:-bottom-0.5
                       after:h-[2px] after:scale-x-0 after:bg-brand-gold
                       after:transition-transform hover:after:scale-x-100"
            >
                عن المدرسة
            </a>

            <a
                href="#stages"
                class="relative py-2 transition
                       hover:text-brand-gold
                       after:absolute after:inset-x-0 after:-bottom-0.5
                       after:h-[2px] after:scale-x-0 after:bg-brand-gold
                       after:transition-transform hover:after:scale-x-100"
            >
                المراحل التعليمية
            </a>

            <a
                href="#admissions"
                class="relative py-2 transition
                       hover:text-brand-gold
                       after:absolute after:inset-x-0 after:-bottom-0.5
                       after:h-[2px] after:scale-x-0 after:bg-brand-gold
                       after:transition-transform hover:after:scale-x-100"
            >
                القبول والتسجيل
            </a>

            <a
                href="#school-life"
                class="relative py-2 transition
                       hover:text-brand-gold
                       after:absolute after:inset-x-0 after:-bottom-0.5
                       after:h-[2px] after:scale-x-0 after:bg-brand-gold
                       after:transition-transform hover:after:scale-x-100"
            >
                الحياة المدرسية
            </a>

            <a
                href="#news"
                class="relative py-2 transition
                       hover:text-brand-gold
                       after:absolute after:inset-x-0 after:-bottom-0.5
                       after:h-[2px] after:scale-x-0 after:bg-brand-gold
                       after:transition-transform hover:after:scale-x-100"
            >
                الأخبار
            </a>

            <a
                href="#contact"
                class="relative py-2 transition
                       hover:text-brand-gold
                       after:absolute after:inset-x-0 after:-bottom-0.5
                       after:h-[2px] after:scale-x-0 after:bg-brand-gold
                       after:transition-transform hover:after:scale-x-100"
            >
                تواصل معنا
            </a>
        </nav>

        {{-- Desktop actions --}}
        <div class="hidden items-center gap-3 xl:flex">

            <button
                type="button"
                class="inline-flex h-9 items-center justify-center
                       rounded-full border border-white/10
                       px-3 text-[12px] font-semibold
                       text-white/70 transition
                       hover:border-brand-gold/40
                       hover:text-white"
            >
                AR | EN
            </button>

            <a
                href="#admissions"
                class="inline-flex h-[44px] items-center justify-center
                       rounded-lg bg-brand-gold px-6
                       text-[15px] font-bold text-brand-navy-dark
                       transition hover:bg-white"
            >
                سجّل الآن
            </a>

        </div>

        {{-- Tablet / Mobile actions --}}
        <div class="flex items-center gap-3 xl:hidden">

            <button
                type="button"
                class="inline-flex h-9 items-center justify-center
                       rounded-full border border-white/10
                       px-3 text-[11px] font-semibold
                       text-white/70"
            >
                AR | EN
            </button>

            <details class="relative" data-header-menu>
                <summary
                    class="flex h-10 w-10 cursor-pointer
                           list-none items-center justify-center
                           rounded-lg border border-white/10
                           text-white transition hover:bg-white/5
                           focus-visible:outline-2
                           focus-visible:outline-offset-2
                           focus-visible:outline-brand-gold
                           [&::-webkit-details-marker]:hidden"
                    aria-label="القائمة"
                    aria-controls="compact-navigation"
                >
                    <img
                        src="{{ asset('images/menu.svg') }}"
                        alt=""
                        width="22"
                        height="22"
                        class="h-[22px] w-[22px]"
                    >
                </summary>

                <nav
                    id="compact-navigation"
                    dir="rtl"
                    aria-label="التنقل الرئيسي"
                    class="absolute left-0 top-[50px]
                           max-h-[calc(100dvh-90px)]
                           w-[250px] overflow-y-auto rounded-xl
                           border border-white/10
                           bg-brand-navy-dark shadow-2xl
                           md:right-0 md:left-auto"
                >
                    <div class="flex flex-col p-2 text-right text-white">

                        <a href="#home" class="rounded-lg px-4 py-3 hover:bg-white/5">
                            الرئيسية
                        </a>

                        <a href="#about" class="rounded-lg px-4 py-3 hover:bg-white/5">
                            عن المدرسة
                        </a>

                        <a href="#stages" class="rounded-lg px-4 py-3 hover:bg-white/5">
                            المراحل التعليمية
                        </a>

                        <a href="#admissions" class="rounded-lg px-4 py-3 hover:bg-white/5">
                            القبول والتسجيل
                        </a>

                        <a href="#school-life" class="rounded-lg px-4 py-3 hover:bg-white/5">
                            الحياة المدرسية
                        </a>

                        <a href="#news" class="rounded-lg px-4 py-3 hover:bg-white/5">
                            الأخبار
                        </a>

                        <a href="#contact" class="rounded-lg px-4 py-3 hover:bg-white/5">
                            تواصل معنا
                        </a>

                    </div>
                </nav>
            </details>

        </div>

    </div>
</header>
