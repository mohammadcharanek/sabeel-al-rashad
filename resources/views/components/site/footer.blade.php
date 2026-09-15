@props(['siteSettings'])

<footer
    id="contact"
    class="bg-[#07111E] text-white"
>
    <div
        class="mx-auto max-w-[1200px]
               px-4 pb-8 pt-14
               md:px-8 md:pt-16"
    >

        <div
            class="grid gap-x-10 gap-y-12
                   md:grid-cols-2"
        >

            {{-- School identity --}}
            <div class="text-right">

                <div class="flex items-center gap-3">

                    <div
                        class="h-12 w-12 overflow-hidden rounded-full
                               ring-2 ring-brand-gold-dark/30"
                    >
                        @if ($logoUrl = $siteSettings->imageUrl('logo', 'images/school-logo.jpg'))
                            <img
                                src="{{ $logoUrl }}"
                                alt="شعار {{ $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد') }}"
                                class="h-full w-full object-cover"
                            >
                        @else
                            <div
                                class="flex h-full w-full
                                       items-center justify-center
                                       bg-white/5
                                       text-[10px] text-white/40"
                            >
                                الشعار
                            </div>
                        @endif
                    </div>

                    <div>
                        <h2
                            class="text-[14px]
                                   font-black leading-[18px]
                                   text-white"
                        >
                            {{ $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد') }}
                        </h2>

                        <p
                            class="mt-0.5 text-[12px]
                                   text-brand-gold-dark/80"
                        >
                            منذ ١٩٨٥
                        </p>
                    </div>

                </div>

                <p
                    class="mt-4 max-w-[290px]
                           text-[12px] leading-[23px]
                           text-white/45"
                >
                    {{ $siteSettings->text('short_description_ar', 'مؤسسة تعليمية رائدة تُضيء طريق المعرفة لأجيال من الطلاب في لبنان والعالم.') }}
                </p>

                <div class="mt-5 flex gap-2">

                    @if ($socialUrl = $siteSettings->linkUrl('facebook_url'))
                        {{-- Facebook --}}
                        <a
                            href="{{ $socialUrl }}"
                            aria-label="Facebook"
                            class="flex h-9 w-9
                                   items-center justify-center
                                   rounded-lg border
                                   border-white/10
                                   bg-white/[0.07]
                                   text-white/70
                                   transition
                                   hover:border-brand-gold-dark
                                   hover:text-brand-gold"
                        >
                            <span class="font-latin text-sm font-bold">f</span>
                        </a>
                    @endif

                    @if ($socialUrl = $siteSettings->linkUrl('instagram_url'))
                        {{-- Instagram --}}
                        <a
                            href="{{ $socialUrl }}"
                            aria-label="Instagram"
                            class="flex h-9 w-9
                                   items-center justify-center
                                   rounded-lg border
                                   border-white/10
                                   bg-white/[0.07]
                                   text-white/70
                                   transition
                                   hover:border-brand-gold-dark
                                   hover:text-brand-gold"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-[18px] w-[18px]"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                                aria-hidden="true"
                            >
                                <rect x="4" y="4" width="16" height="16" rx="5" />
                                <circle cx="12" cy="12" r="3.5" />
                                <circle
                                    cx="17.2"
                                    cy="6.8"
                                    r="0.8"
                                    fill="currentColor"
                                    stroke="none"
                                />
                            </svg>
                        </a>
                    @endif

                    @if ($socialUrl = $siteSettings->linkUrl('youtube_url'))
                        {{-- YouTube --}}
                        <a
                            href="{{ $socialUrl }}"
                            aria-label="YouTube"
                            class="flex h-9 w-9
                                   items-center justify-center
                                   rounded-lg border
                                   border-white/10
                                   bg-white/[0.07]
                                   text-white/70
                                   transition
                                   hover:border-brand-gold-dark
                                   hover:text-brand-gold"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-[18px] w-[18px]"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path d="M20.5 7.2c-.2-1.3-1-2.1-2.3-2.3C16.7 4.6 14.4 4.5 12 4.5s-4.7.1-6.2.4C4.5 5.1 3.7 5.9 3.5 7.2 3.2 8.5 3.1 10.2 3.1 12s.1 3.5.4 4.8c.2 1.3 1 2.1 2.3 2.3 1.5.3 3.8.4 6.2.4s4.7-.1 6.2-.4c1.3-.2 2.1-1 2.3-2.3.3-1.3.4-3 .4-4.8s-.1-3.5-.4-4.8ZM10.3 15.2V8.8l5.2 3.2-5.2 3.2Z" />
                            </svg>
                        </a>
                    @endif


                </div>

            </div>

            {{-- Quick links --}}
            <div class="text-right">

                <h3 class="text-[14px] font-black">
                    روابط سريعة
                </h3>

                <nav class="mt-4 space-y-3">

                    @php
                        $footerLinks = [
                            ['الرئيسية', '#home'],
                            ['عن المدرسة', '#about'],
                            ['المراحل التعليمية', '#stages'],
                            ['القبول والتسجيل', '#admissions'],
                            ['الحياة المدرسية', '#school-life'],
                            ['الأخبار', '#news'],
                            ['تواصل معنا', '#contact'],
                        ];
                    @endphp

                    @foreach ($footerLinks as [$label, $href])
                        <a
                            href="{{ $href }}"
                            class="flex items-center gap-2
                                   text-[12px]
                                   text-white/50
                                   transition
                                   hover:text-brand-gold"
                        >
                            <span
                                class="text-brand-gold-dark/70"
                                aria-hidden="true"
                            >
                                ‹
                            </span>

                            <span>{{ $label }}</span>
                        </a>
                    @endforeach

                </nav>

            </div>

            {{-- Contact information --}}
            <div class="text-right">

                <h3 class="text-[14px] font-black">
                    معلومات التواصل
                </h3>

                <div class="mt-4 space-y-4">

                    <a
                        href="tel:{{ $siteSettings->text('primary_phone', '+96108630336') }}"
                        class="flex items-center gap-3
                               text-[12px] font-semibold
                               text-white/80
                               transition
                               hover:text-brand-gold"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-[17px] w-[17px]
                                   shrink-0 text-brand-gold-dark"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.6"
                            aria-hidden="true"
                        >
                            <path d="M7 4h3l1 4-2 1c1 3 3 5 6 6l1-2 4 1v3c0 1-1 2-2 2C10 19 5 14 5 6c0-1 1-2 2-2Z" />
                        </svg>

                        <span dir="ltr">
                            {{ $siteSettings->text('primary_phone', '+961 08 630 336') }}
                        </span>
                    </a>

                    @if (filled($siteSettings->secondary_phone))
                        <a href="tel:{{ $siteSettings->secondary_phone }}" class="flex items-center gap-3 text-[12px] text-white/65 transition hover:text-brand-gold">
                            <span dir="ltr">{{ $siteSettings->secondary_phone }}</span>
                        </a>
                    @endif

                    <a
                        href="mailto:{{ $siteSettings->text('contact_email', 'info@sabeelalrashad.com') }}"
                        class="flex items-center gap-3
                               text-[12px] text-white/65
                               transition
                               hover:text-brand-gold"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-[17px] w-[17px]
                                   shrink-0 text-brand-gold-dark"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.6"
                            aria-hidden="true"
                        >
                            <rect x="3" y="5" width="18" height="14" rx="2" />
                            <path d="m4 7 8 6 8-6" />
                        </svg>

                        <span dir="ltr">
                            {{ $siteSettings->text('contact_email', 'info@sabeelalrashad.com') }}
                        </span>
                    </a>

                    <div
                        class="flex items-start gap-3
                               text-[12px] leading-5
                               text-white/50"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="mt-0.5 h-[17px] w-[17px]
                                   shrink-0 text-brand-gold-dark"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.6"
                            aria-hidden="true"
                        >
                            <path d="M12 21s6-5.2 6-11a6 6 0 1 0-12 0c0 5.8 6 11 6 11Z" />
                            <circle cx="12" cy="10" r="2" />
                        </svg>

                        <span>
                            {{ $siteSettings->text('address_ar', 'لبنان، البقاع الغربي، القرعون، جب جنين') }}
                        </span>
                    </div>

                </div>

            </div>

            {{-- Newsletter --}}
            <div class="text-right">

                <h3 class="text-[14px] font-black">
                    النشرة الإخبارية
                </h3>

                <p
                    class="mt-4 text-[12px]
                           leading-[21px]
                           text-white/45"
                >
                    اشترك ليصلك آخر أخبار وفعاليات المدرسة
                    إلى بريدك مباشرة.
                </p>

                {{-- Visual only until newsletter backend is implemented --}}
                <div class="mt-4 flex gap-2">

                    <input
                        type="email"
                        name="email"
                        placeholder="بريدك الإلكتروني"
                        class="h-[42px] min-w-0 flex-1
                               rounded-lg border
                               border-white/10
                               bg-white/[0.07]
                               px-4
                               text-[12px] text-white
                               outline-none
                               placeholder:text-white/40
                               focus:border-brand-gold-dark"
                    >

                    <button
                        type="button"
                        class="h-[42px]
                               shrink-0 rounded-lg
                               bg-brand-gold-dark
                               px-4
                               text-[12px] font-bold
                               text-white
                               transition
                               hover:bg-brand-gold"
                    >
                        اشترك
                    </button>

                </div>

            </div>

        </div>

        {{-- Bottom bar --}}
        <div
            class="mt-14 border-t
                   border-white/[0.07]
                   pt-6"
        >
            <div
                class="flex flex-col gap-3
                       text-center
                       md:flex-row
                       md:items-center
                       md:justify-between
                       md:text-right"
            >

                <p
                    class="text-[12px]
                           text-white/30"
                >
                    © {{ date('Y') }} {{ $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد') }} — جميع الحقوق محفوظة
                </p>

                <p
                    dir="ltr"
                    class="font-latin
                           text-[12px]
                           tracking-[0.02em]
                           text-white/30"
                >
                    since 1985 · S.R.S lighting up the educational path
                </p>

            </div>
        </div>

    </div>
</footer>
