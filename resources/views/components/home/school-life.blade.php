@props(['siteSettings', 'galleryItems'])

<section id="school-life" class="bg-brand-navy py-16 md:py-24">
    <div class="mx-auto max-w-[1200px] px-4 md:px-8">
        <x-home.section-title
            eyebrow="معرض الصور"
            :title="'الحياة في '.($siteSettings->exists ? $siteSettings->text('school_name_ar', 'سبيل الرشاد') : 'سبيل الرشاد')"
            description="صور من الحياة المدرسية"
            :dark="true"
        />

        @if ($galleryItems->isEmpty())
            <p class="mt-8 rounded-xl border border-white/20 px-6 py-8 text-center text-white/80">
                ستُعرض صور الحياة المدرسية هنا بعد اعتمادها ونشرها.
            </p>
        @else
            {{-- A single ordered collection powers all three breakpoints. --}}
            <div class="mt-8 grid grid-cols-2 gap-3 md:mt-12 lg:grid-cols-4">
                @foreach ($galleryItems as $item)
                    <figure @class([
                        'group relative overflow-hidden rounded-xl bg-white/5',
                        'col-span-2 h-[260px] md:h-[350px] lg:row-span-2 lg:h-[628px]' => $loop->first,
                        'h-[190px] md:h-[260px] lg:h-[306px]' => ! $loop->first,
                    ])>
                        <img
                            src="{{ $item->imageUrl() }}"
                            alt="{{ $item->alt_text ?: ($item->title ?: 'صورة من الحياة المدرسية') }}"
                            loading="lazy"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                        >
                        @if (filled($item->caption))
                            <figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 to-transparent p-4 text-sm text-white">
                                {{ $item->caption }}
                            </figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        @endif
    </div>
</section>
