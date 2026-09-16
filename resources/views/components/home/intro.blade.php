@props(['siteSettings', 'homepageSettings'])

<section
    id="about"
    class="bg-white py-16 md:py-24"
>
    <div class="mx-auto max-w-[1200px] px-4 md:px-8">

        <div
            class="grid items-center gap-12
                   lg:grid-cols-2"
        >

            {{-- Image --}}
            <div class="relative order-2 lg:order-1">

                <div
                    class="relative min-h-[260px] overflow-hidden
                           rounded-2xl bg-[#F4F7FB]
                           shadow-[0_20px_60px_rgba(14,35,64,0.15)]
                           md:min-h-[360px]
                           lg:min-h-[440px]"
                >
                    @if ($introImage = $homepageSettings->imageUrl('intro_image', 'images/school-building.jpg'))

                        <img
                            src="{{ $introImage }}"
                            alt="مبنى {{ $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد') }}"
                            loading="lazy"
                            width="900"
                            height="700"
                            class="absolute inset-0
                                   h-full w-full
                                   object-cover
                                   object-center"
                        >

                        {{-- Subtle image overlay --}}
                        <div
                            class="absolute inset-0
                                   bg-gradient-to-t
                                   from-brand-navy-dark/20
                                   via-transparent
                                   to-transparent"
                            aria-hidden="true"
                        ></div>

                    @else

                        <div
                            class="absolute inset-0
                                   flex flex-col
                                   items-center justify-center
                                   gap-3
                                   bg-[#F4F7FB]
                                   px-6 text-center"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                                class="h-10 w-10 text-brand-navy/30"
                                aria-hidden="true"
                            >
                                <rect
                                    x="3"
                                    y="5"
                                    width="18"
                                    height="14"
                                    rx="2"
                                />
                                <circle
                                    cx="9"
                                    cy="10"
                                    r="1.5"
                                />
                                <path d="m5 17 4-4 3 3 2-2 5 3" />
                            </svg>

                            <p
                                class="text-sm font-semibold
                                       text-text-muted"
                            >
                                صورة المدرسة
                            </p>
                        </div>

                    @endif
                </div>

                {{-- Experience badge --}}
                <div
                    class="absolute -bottom-5 left-4
                           rounded-xl border border-[#DDE4ED]
                           bg-white px-5 py-4
                           shadow-[0_8px_15px_rgba(0,0,0,0.12)]
                           md:-left-4"
                >
                    <div
                        class="text-[30px] font-black
                               leading-[30px] text-brand-navy"
                    >
                        +٤٠
                    </div>

                    <div
                        class="mt-1 text-[12px]
                               font-semibold text-[#607080]"
                    >
                        {{ $homepageSettings->text('experience_label', 'عاماً من التميز') }}
                    </div>
                </div>

            </div>

            {{-- Content --}}
            <div class="order-1 text-right lg:order-2">

                <span
                    class="inline-flex rounded-full
                           border border-brand-gold-dark/25
                           bg-brand-gold-dark/10
                           px-3 py-1
                           text-[12px] font-bold
                           tracking-wide text-brand-gold-dark"
                >
                    {{ $homepageSettings->text('intro_eyebrow', 'تأسست عام ١٩٨٥') }}
                </span>

                <h2
                    class="mt-4 text-[28px]
                           font-black leading-[1.3]
                           text-brand-navy
                           md:text-[32px]"
                >
                    @if (filled($homepageSettings->intro_title))
                        {{ $homepageSettings->intro_title }}
                    @else
                        مرحباً بكم في
                        <br>
                        {{ $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد') }}
                    @endif
                </h2>

                <p
                    class="mt-5 max-w-[500px]
                           text-[16px] leading-[1.8]
                           text-[#607080]"
                >
                    {{ $homepageSettings->text('intro_paragraph_one', $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد').' مؤسسة تعليمية لبنانية عريقة تأسست عام ١٩٨٥ في جب جنين — البقاع الغربي. نلتزم بتقديم تعليم أصيل يجمع التفوق الأكاديمي مع تنمية القيم والأخلاق الرفيعة.') }}
                </p>

                <p
                    class="mt-4 max-w-[500px]
                           text-[16px] leading-[1.8]
                           text-[#607080]"
                >
                    {{ $homepageSettings->text('intro_paragraph_two', 'نسعى إلى بناء جيل واعٍ وقادر على مواجهة تحديات العصر بثقة وتميز، في بيئة تعليمية آمنة يقودها كادر تربوي متخصص يؤمن برسالة التعليم.') }}
                </p>

                {{-- Vision / Mission --}}
                <div
                    class="mt-8 grid gap-5
                           sm:grid-cols-2"
                >

                    <div
                        class="rounded-xl
                               border border-[#DDE4ED]
                               bg-[#F4F7FB]
                               p-4"
                    >
                        <div
                            class="text-[12px] font-bold
                                   text-brand-gold-dark"
                        >
                            رؤيتنا
                        </div>

                        <p
                            class="mt-1 text-[14px]
                                   font-semibold leading-5
                                   text-brand-navy"
                        >
                            مدرسة رائدة في التميز التعليمي
                        </p>
                    </div>

                    <div
                        class="rounded-xl
                               border border-[#DDE4ED]
                               bg-[#F4F7FB]
                               p-4"
                    >
                        <div
                            class="text-[12px] font-bold
                                   text-brand-gold-dark"
                        >
                            رسالتنا
                        </div>

                        <p
                            class="mt-1 text-[14px]
                                   font-semibold leading-5
                                   text-brand-navy"
                        >
                            تنشئة جيل مؤهل ومتوازن
                        </p>
                    </div>

                </div>

                @if ($stagesUrl = $homepageSettings->sectionUrl('stages'))
                <div class="mt-9">
                    <a
                        href="{{ $stagesUrl }}"
                        class="inline-flex h-[46px]
                               items-center justify-center
                               rounded-lg bg-brand-navy
                               px-7 text-[16px]
                               font-semibold text-white
                               transition
                               hover:bg-brand-navy-dark"
                    >
                        تعرف على مدرستنا
                    </a>
                </div>
                @endif

            </div>

        </div>

    </div>
</section>
