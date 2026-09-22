@php
    $schoolName = $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد');
    $title = ($folder?->title ?? 'معرض الصور').' | '.$schoolName;
    $description = 'صور الحياة المدرسية والأنشطة والفعاليات في '.$schoolName;
@endphp

<x-layouts.app :site-settings="$siteSettings" :homepage-settings="$homepageSettings" :title="$title" :description="$description">
    <div class="bg-brand-navy px-4 py-14 text-center text-white md:py-20">
        <nav aria-label="مسار التنقل" class="mb-5 text-sm text-white/70"><a href="{{ route('home') }}" class="inline-flex min-h-11 min-w-11 items-center justify-center underline underline-offset-4 hover:text-white">الرئيسية</a> <span aria-hidden="true"> / </span> <a href="{{ route('gallery.index') }}" class="inline-flex min-h-11 min-w-11 items-center justify-center underline underline-offset-4 hover:text-white">معرض الصور</a> @if($folder) <span aria-hidden="true"> / </span> {{ $folder->title }} @endif</nav>
        <h1 class="text-3xl font-black md:text-5xl">{{ $folder?->title ?? 'معرض الصور' }}</h1>
        <p class="mx-auto mt-4 max-w-2xl text-base leading-8 text-white/75">لقطات من الأنشطة والفعاليات والحياة المدرسية في {{ $schoolName }}.</p>
        <a href="{{ route('videos.index') }}" class="mt-5 inline-flex min-h-11 items-center font-bold text-brand-gold underline underline-offset-4">معرض الفيديو</a>
    </div>

    <section class="mx-auto max-w-[1200px] px-4 py-12 md:px-8 md:py-16" aria-label="صور الحياة المدرسية">
        <x-site.media-folders :folders="$folders" :folder="$folder" index-route="gallery.index" show-route="gallery.show" />
        @if (! $folder && $galleryItems->isNotEmpty())
            <h2 class="mb-6 text-2xl font-bold text-brand-navy">صور غير مصنفة</h2>
        @endif
        @if ($galleryItems->isEmpty())
            <p class="rounded-2xl border border-border-soft bg-[#F4F7FB] p-10 text-center text-text-muted">{{ $folder ? 'لا توجد صور معتمدة ومنشورة في هذا المجلد بعد.' : ($folders->isNotEmpty() ? 'اختر مجلداً لمشاهدة الصور المنشورة.' : 'ستُعرض صور الحياة المدرسية هنا بعد اعتمادها ونشرها.') }}</p>
        @else
            <div id="gallery-slideshow" data-gallery-slideshow tabindex="0" class="overflow-hidden rounded-2xl border border-border-soft bg-brand-navy text-white" aria-roledescription="عرض شرائح" aria-label="صور المدرسة">
                <div class="relative">
                    @foreach ($galleryItems as $item)
                        <figure data-gallery-slide @if (! $loop->first) hidden @endif class="relative">
                            <img src="{{ $item->imageUrl() }}" alt="{{ $item->alt_text ?: ($item->title ?: 'صورة من الحياة المدرسية') }}" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif class="h-[260px] w-full object-contain sm:h-[420px] lg:h-[600px]">
                            @if (filled($item->title) || filled($item->caption))
                                <figcaption class="bg-brand-navy-dark px-5 py-4 text-right leading-7">
                                    @if(filled($item->title)) <strong class="block text-base">{{ $item->title }}</strong> @endif
                                    @if(filled($item->caption)) <span class="block text-sm text-white/75">{{ $item->caption }}</span> @endif
                                </figcaption>
                            @endif
                        </figure>
                    @endforeach
                </div>
                @if($galleryItems->count() > 1)
                    <div class="flex items-center justify-between gap-3 px-4 py-4 md:px-6">
                        <button type="button" data-gallery-prev class="min-h-11 rounded-lg border border-white/40 px-4 py-2 font-bold hover:bg-white/10" aria-label="الصورة السابقة">السابق</button>
                        <span data-gallery-counter dir="ltr" class="text-sm" role="status" aria-live="polite">1 / {{ $galleryItems->count() }}</span>
                        <button type="button" data-gallery-next class="min-h-11 rounded-lg border border-white/40 px-4 py-2 font-bold hover:bg-white/10" aria-label="الصورة التالية">التالي</button>
                    </div>
                @endif
            </div>

            <h2 class="mt-14 text-2xl font-bold text-brand-navy">تصفح جميع الصور</h2>
            <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4" aria-label="شبكة الصور">
                @foreach ($galleryItems as $item)
                    <button type="button" data-gallery-go-to="{{ $loop->index }}" class="group overflow-hidden rounded-xl border border-border-soft bg-white text-right focus-visible:outline focus-visible:outline-4 focus-visible:outline-brand-gold" aria-label="عرض الصورة {{ $loop->iteration }}: {{ $item->alt_text ?: ($item->title ?: 'صورة من الحياة المدرسية') }}" aria-controls="gallery-slideshow">
                        <img src="{{ $item->imageUrl() }}" alt="" loading="lazy" class="h-32 w-full object-cover transition group-hover:scale-[1.03] sm:h-44">
                        <span class="block px-3 py-3 text-sm font-semibold text-brand-navy">{{ $item->title ?: ($item->caption ?: 'صورة من الحياة المدرسية') }}</span>
                    </button>
                @endforeach
            </div>
        @endif
    </section>
    <script src="{{ asset('js/gallery.js') }}" defer></script>
</x-layouts.app>
