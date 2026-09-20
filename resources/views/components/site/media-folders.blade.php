@props(['folders', 'indexRoute', 'showRoute', 'folder' => null])

<nav aria-label="مجلدات الوسائط" class="mb-10">
    @if ($folder)
        <a href="{{ route($indexRoute) }}" class="mb-6 inline-flex min-h-11 items-center font-bold text-brand-navy underline underline-offset-4">العودة إلى جميع المجلدات</a>
    @endif
    @if ($folders->isNotEmpty())
        <h2 class="mb-5 text-2xl font-bold text-brand-navy">تصفح المجلدات</h2>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($folders as $album)
                <a href="{{ route($showRoute, ['folder' => $album->slug]) }}" @if($folder?->is($album)) aria-current="page" @endif class="overflow-hidden rounded-2xl border border-border-soft bg-white transition hover:border-brand-gold focus-visible:outline focus-visible:outline-4 focus-visible:outline-brand-gold">
                    @if ($coverUrl = $album->coverUrl())
                        <img src="{{ $coverUrl }}" alt="غلاف مجلد {{ $album->title }}" loading="lazy" class="h-44 w-full object-cover">
                    @endif
                    <div class="p-5">
                        <h3 class="text-lg font-bold text-brand-navy">{{ $album->title }}</h3>
                        @if (filled($album->description))
                            <p class="mt-2 text-sm leading-7 text-text-muted">{{ $album->description }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</nav>
