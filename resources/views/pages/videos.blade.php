@php
    $schoolName = $siteSettings->text('school_name_ar', 'ثانوية سبيل الرشاد');
    $title = ($folder?->title ?? 'معرض الفيديو').' | '.$schoolName;
    $description = 'فيديوهات الحياة المدرسية والأنشطة والفعاليات في '.$schoolName;
@endphp

<x-layouts.app :site-settings="$siteSettings" :homepage-settings="$homepageSettings" :title="$title" :description="$description">
    <div class="bg-brand-navy px-4 py-14 text-center text-white md:py-20">
        <nav aria-label="مسار التنقل" class="mb-5 text-sm text-white/70">
            <a href="{{ route('home') }}" class="inline-flex min-h-11 min-w-11 items-center justify-center underline underline-offset-4 hover:text-white">الرئيسية</a>
            <span aria-hidden="true"> / </span>
            <a href="{{ route('videos.index') }}" class="inline-flex min-h-11 min-w-11 items-center justify-center underline underline-offset-4 hover:text-white">معرض الفيديو</a>
            @if ($folder) <span aria-hidden="true"> / </span> {{ $folder->title }} @endif
        </nav>
        <h1 class="text-3xl font-black md:text-5xl">{{ $folder?->title ?? 'معرض الفيديو' }}</h1>
        <p class="mx-auto mt-4 max-w-2xl text-base leading-8 text-white/75">فيديوهات الأنشطة والحياة المدرسية في {{ $schoolName }}.</p>
        <a href="{{ route('gallery.index') }}" class="mt-5 inline-flex min-h-11 items-center font-bold text-brand-gold underline underline-offset-4">معرض الصور</a>
    </div>

    <section class="mx-auto max-w-[1200px] px-4 py-12 md:px-8 md:py-16" aria-label="فيديوهات الحياة المدرسية">
        <x-site.media-folders :folders="$folders" :folder="$folder" index-route="videos.index" show-route="videos.show" />
        @if (! $folder && $videos->isNotEmpty())
            <h2 class="mb-6 text-2xl font-bold text-brand-navy">فيديوهات غير مصنفة</h2>
        @endif
        @if ($videos->isEmpty())
            <p class="rounded-2xl border border-border-soft bg-[#F4F7FB] p-10 text-center text-text-muted">{{ $folder ? 'لا توجد فيديوهات معتمدة ومنشورة في هذا المجلد بعد.' : ($folders->isNotEmpty() ? 'اختر مجلداً لمشاهدة الفيديوهات المنشورة.' : 'ستُعرض فيديوهات الحياة المدرسية هنا بعد اعتمادها ونشرها.') }}</p>
        @else
            <div class="grid gap-8 md:grid-cols-2">
                @foreach ($videos as $video)
                    <figure class="overflow-hidden rounded-2xl border border-border-soft bg-white">
                        <video controls playsinline preload="none" @if($posterUrl = $video->posterUrl()) poster="{{ $posterUrl }}" @endif aria-labelledby="video-title-{{ $video->id }}" @if(filled($video->description)) aria-describedby="video-description-{{ $video->id }}" @endif class="aspect-video w-full bg-brand-navy object-contain">
                            <source src="{{ $video->videoUrl() }}" type="{{ $video->mimeType() }}">
                            متصفحك لا يدعم تشغيل الفيديو. <a href="{{ $video->videoUrl() }}">تنزيل {{ $video->title }}</a>
                        </video>
                        <figcaption class="p-5">
                            <h2 id="video-title-{{ $video->id }}" class="text-xl font-bold text-brand-navy">{{ $video->title }}</h2>
                            @if (filled($video->description))
                                <p id="video-description-{{ $video->id }}" class="mt-3 whitespace-pre-line leading-8 text-text-muted">{{ $video->description }}</p>
                            @endif
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.app>
