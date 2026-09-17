@props(['siteSettings', 'galleryItems'])

<section id="school-life" class="bg-brand-navy py-16 md:py-24">
    <div class="mx-auto max-w-[1200px] px-4 md:px-8">

        <x-home.section-title
            eyebrow="الحياة المدرسية"
            :title="'الحياة في '.($siteSettings->exists ? $siteSettings->text('school_name_ar', 'سبيل الرشاد') : 'سبيل الرشاد')"
            description="اكتشف أنشطتنا وحياتنا المدرسية من خلال معرض الصور."
            :dark="true"
        />

        @if ($galleryItems->isNotEmpty())
            @php
                $preview = $galleryItems->first();
            @endphp

            <a
                href="{{ route('gallery.index') }}"
                class="group relative mt-10 block overflow-hidden rounded-2xl border border-white/20 bg-white/5 focus-visible:outline focus-visible:outline-4 focus-visible:outline-brand-gold"
                aria-label="عرض معرض الصور الكامل"
            >
                <img
                    src="{{ $preview->imageUrl() }}"
                    alt="{{ $preview->alt_text ?: ($preview->title ?: 'صورة من الحياة المدرسية') }}"
                    loading="lazy"
                    class="h-[260px] w-full object-cover transition duration-500 group-hover:scale-[1.02] md:h-[390px]"
                >

                <div
                    class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/20 to-transparent"
                    aria-hidden="true"
                ></div>

                <div class="absolute inset-x-0 bottom-0 flex flex-wrap items-end justify-between gap-4 p-6 text-white md:p-9">
                    <div>
                        <p class="text-sm text-white/80">
                            صور وأنشطة من المدرسة
                        </p>

                        <h3 class="mt-1 text-2xl font-bold">
                            معرض الصور
                        </h3>
                    </div>

                    <span class="inline-flex min-h-11 items-center rounded-lg bg-brand-gold px-6 py-3 text-sm font-bold text-brand-navy-dark">
                        استعرض جميع الصور ←
                    </span>
                </div>
            </a>
        @else
            <p class="mt-8 text-center leading-8 text-white/80">
                ستُعرض صور الحياة المدرسية هنا بعد اعتمادها ونشرها.
            </p>

            <div class="mt-6 text-center">
                <a
                    href="{{ route('gallery.index') }}"
                    class="inline-flex min-h-11 items-center rounded-lg border border-white/40 px-6 py-3 font-bold text-white transition hover:bg-white/10 focus-visible:outline focus-visible:outline-4 focus-visible:outline-brand-gold"
                >
                    زيارة معرض الصور
                </a>
            </div>
        @endif

    </div>
</section>