@props(['siteSettings', 'homepageSettings'])

<section
    id="admissions"
    class="relative overflow-hidden bg-brand-navy"
>
    {{-- Subtle center glow --}}
    <div
        class="pointer-events-none absolute inset-0 opacity-5"
        style="
            background:
                radial-gradient(
                    ellipse at center,
                    rgba(255,255,255,1) 0%,
                    rgba(255,255,255,0) 55%
                );
        "
        aria-hidden="true"
    ></div>

    {{-- Gold top accent --}}
    <div
        class="absolute inset-x-0 top-0 h-1
               bg-gradient-to-r
               from-transparent
               via-brand-gold-dark
               to-transparent"
        aria-hidden="true"
    ></div>

    <div
        class="relative mx-auto flex
               min-h-[470px] max-w-[768px]
               items-center justify-center
               px-4 py-[70px]
               md:min-h-[410px] md:px-8 md:py-[82px]"
    >

        <div class="w-full text-center">

            {{-- Pill --}}
            <span
                class="inline-flex min-h-[26px]
                       items-center justify-center
                       rounded-full
                       border border-brand-gold-dark/25
                       bg-brand-gold-dark/10
                       px-4
                       text-[12px] font-bold
                       tracking-[0.04em]
                       text-brand-gold-dark"
            >
                {{ $homepageSettings->text('admissions_eyebrow', 'القبول والتسجيل') }}
            </span>

            {{-- Heading --}}
            <h2
                class="mx-auto mt-4
                       max-w-[704px]
                       text-[32px] font-black
                       leading-[1.3]
                       text-white
                       md:text-[36px]"
            >
                {{ $homepageSettings->text('admissions_title', 'ابدأ رحلة طفلك التعليمية معنا') }}
            </h2>

            {{-- Description --}}
            <p
                class="mx-auto mt-4
                       max-w-[480px]
                       text-[16px]
                       leading-[1.8]
                       text-white/65"
            >
                {{ $homepageSettings->text('admissions_description', 'التسجيل للعام الدراسي الجديد متاح الآن. انضم إلى عائلة '.($siteSettings->exists ? $siteSettings->text('school_name_ar', 'سبيل الرشاد') : 'سبيل الرشاد').' واستثمر في مستقبل طفلك.') }}
            </p>

            {{-- Actions --}}
            <div
                class="mx-auto mt-10
                       flex max-w-[326px]
                       flex-col gap-3
                       sm:max-w-none
                       sm:flex-row
                       sm:justify-center
                       sm:gap-4"
            >

                <a
                    href="{{ $homepageSettings->linkUrl('admissions_primary_url', '#contact') }}"
                    class="inline-flex h-[46px]
                           w-full items-center justify-center
                           rounded-lg
                           bg-brand-gold
                           px-7
                           text-[16px] font-semibold
                           text-brand-navy-dark
                           transition
                           hover:bg-brand-gold-dark
                           hover:text-white
                           sm:w-[125px]"
                >
                    {{ $homepageSettings->text('admissions_primary_label', 'سجّل الآن') }}
                </a>

                <a
                    href="{{ $homepageSettings->linkUrl('admissions_secondary_url', '#contact') }}"
                    class="inline-flex h-[46px]
                           w-full items-center justify-center
                           rounded-lg
                           border-[1.5px]
                           border-white/65
                           px-7
                           text-[16px] font-semibold
                           text-white
                           transition
                           hover:border-white
                           hover:bg-white
                           hover:text-brand-navy
                           sm:w-[136px]"
                >
                    {{ $homepageSettings->text('admissions_secondary_label', 'تواصل معنا') }}
                </a>

            </div>

        </div>

    </div>
</section>
