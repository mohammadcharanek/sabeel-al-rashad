@props(['siteSettings', 'homepageSettings'])

<section
    id="principal-message"
    class="bg-brand-navy py-16 md:py-20 lg:py-24"
>
    <div class="mx-auto max-w-[1200px] px-4 md:px-8">

        <div
            class="overflow-hidden rounded-2xl
                   border border-white/10
                   bg-white/[0.04]"
        >

            <div
                class="grid
                       lg:grid-cols-2"
            >

                {{-- Principal image --}}
                <div
                    class="relative order-1
                           h-[260px]
                           md:h-[360px]
                           lg:order-2 lg:h-[420px]"
                >

                    @if ($principalImage = $siteSettings->imageUrl('principal_photo', 'images/principal.jpg'))
                        <img
                            src="{{ $principalImage }}"
                            alt="{{ $siteSettings->text('principal_title_ar', 'مدير '.$siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد')) }}"
                            class="h-full w-full object-cover"
                        >
                    @else
                        <div
                            class="flex h-full w-full
                                   items-center justify-center
                                   bg-white/5
                                   text-sm text-white/40"
                        >
                            صورة مدير المدرسة
                        </div>
                    @endif

                    <div
                        class="absolute inset-0
                               bg-gradient-to-l
                               from-brand-navy/45
                               to-transparent"
                        aria-hidden="true"
                    ></div>

                </div>

                {{-- Message --}}
                <div
                    class="order-2 flex
                           min-h-[410px]
                           flex-col justify-center
                           bg-white/[0.04]
                           px-6 py-12
                           md:px-10
                           lg:order-1 lg:min-h-[420px] lg:px-12"
                >

                    {{-- Quote icon --}}
                    <div
                        class="text-[58px]
                               font-black leading-none
                               text-white/15"
                        aria-hidden="true"
                    >
                        “
                    </div>

                    {{-- Gold pill --}}
                    <div class="mt-2">
                        <span
                            class="inline-flex
                                   min-h-[26px]
                                   items-center
                                   rounded-full
                                   border border-brand-gold-dark/25
                                   bg-brand-gold-dark/10
                                   px-3
                                   text-[12px]
                                   font-bold
                                   tracking-[0.04em]
                                   text-brand-gold-dark"
                        >
                            {{ $homepageSettings->text('principal_eyebrow', 'كلمة مدير المدرسة') }}
                        </span>
                    </div>

                    {{-- Message text --}}
                    <p
                        class="mt-6
                               max-w-[500px]
                               text-[16px]
                               leading-8
                               text-white/85"
                    >
                        {{ $siteSettings->text('principal_message_ar', 'يسعدني أن أرحب بكم في '.$siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد').'، هذه المؤسسة التربوية التي نفخر بها ونُكرّس جهودنا لتقديم تعليم راقٍ يُهيئ أبناءنا لمستقبل مشرق مليء بالتميز والإنجاز.') }}
                    </p>

                    {{-- Principal identity --}}
                    <div
                        class="mt-8 flex items-center gap-4"
                    >

                        <div
                            class="h-14 w-14 shrink-0
                                   overflow-hidden rounded-full
                                   border-[1.6px]
                                   border-brand-gold-dark"
                        >
                            @if ($principalImage)
                                <img
                                    src="{{ $principalImage }}"
                                    alt="{{ $siteSettings->text('principal_name_ar', 'أ. إبراهيم العزنكي') }}"
                                    class="h-full w-full object-cover"
                                >
                            @else
                                <div
                                    class="flex h-full w-full
                                           items-center justify-center
                                           bg-white/10
                                           text-[10px] text-white/50"
                                >
                                    الصورة
                                </div>
                            @endif
                        </div>

                        <div class="text-right">

                            <h3
                                class="text-[14px]
                                       font-black
                                       leading-5
                                       text-white"
                            >
                                {{ $siteSettings->text('principal_name_ar', 'أ. إبراهيم العزنكي') }}
                            </h3>

                            <p
                                class="mt-1
                                       text-[12px]
                                       leading-4
                                       text-[#D4A843]"
                            >
                                {{ $siteSettings->text('principal_title_ar', 'مدير '.$siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد')) }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
</section>
