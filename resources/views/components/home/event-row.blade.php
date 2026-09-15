@props([
    'day',
    'month',
    'title',
    'description' => null,
])

<article
    {{ $attributes->class([
        'flex min-h-[110px] items-center gap-5 rounded-xl',
        'border border-[#E5E7EB] bg-white px-6 py-5',
        'text-right',
    ]) }}
>
    <div
        class="flex h-16 w-16 shrink-0 flex-col
               items-center justify-center rounded-xl
               bg-brand-navy text-white"
    >
        <span class="text-[19px] font-bold leading-none">
            {{ $day }}
        </span>

        <span class="mt-1 text-[11px] leading-none">
            {{ $month }}
        </span>
    </div>

    <div class="min-w-0">
        <h3 class="text-[18px] font-bold text-brand-navy">
            {{ $title }}
        </h3>

        @if ($description)
            <p class="mt-2 text-[13px] leading-6 text-text-muted">
                {{ $description }}
            </p>
        @endif
    </div>
</article>