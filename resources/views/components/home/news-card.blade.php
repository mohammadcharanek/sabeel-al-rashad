@props([
    'title',
    'excerpt' => null,
    'category' => null,
    'date' => null,
    'image' => null,
    'href' => null,
])

<article
    {{ $attributes->class([
        'flex h-full flex-col overflow-hidden rounded-[14px]',
        'border border-[#E5E7EB] bg-white',
        'transition duration-300',
        'hover:-translate-y-1 hover:shadow-[0_16px_36px_rgba(14,35,64,0.08)]',
    ]) }}
>

    {{-- News image --}}
    <div class="h-[176px] overflow-hidden bg-[#F4F7FB]">

        @if ($image)
            <img
                src="{{ $image }}"
                alt="{{ $title }}"
                loading="lazy"
                class="h-full w-full object-cover transition duration-500 hover:scale-[1.03]"
            >
        @else
            <div
                class="flex h-full items-center justify-center
                       bg-[#EEF1F5]
                       text-sm text-text-muted/60"
            >
                صورة الخبر
            </div>
        @endif

    </div>

    {{-- Card content --}}
    <div class="flex flex-1 flex-col px-[23px] pb-6 pt-[22px]">

        {{-- Category + date --}}
        @if ($category || $date)
            <div class="flex items-center justify-between gap-3">

                @if ($category)
                    <span
                        class="inline-flex min-h-[26px]
                               items-center justify-center
                               rounded-full bg-[#F7EAD1]
                               px-[14px] py-1
                               text-[12px] font-semibold
                               text-brand-gold-dark"
                    >
                        {{ $category }}
                    </span>
                @endif

                @if ($date)
                    <span
                        class="text-[13px]
                               font-semibold
                               text-[#6B7380]"
                    >
                        {{ $date }}
                    </span>
                @endif

            </div>
        @endif

        {{-- Title --}}
        <h3
            class="mt-4 text-[20px]
                   font-bold leading-8
                   text-brand-navy"
        >
            {{ $title }}
        </h3>

        {{-- Excerpt --}}
        @if ($excerpt)
            <p
                class="mt-2 min-h-[62px]
                       text-[14px] leading-6
                       text-text-muted"
            >
                {{ $excerpt }}
            </p>
        @endif

        {{-- Read more --}}
        @if ($href)
            <a
                href="{{ $href }}"
                class="mt-auto inline-flex
                       items-center gap-2 pt-4
                       text-[14px] font-semibold
                       text-brand-gold-dark
                       transition hover:text-brand-navy"
            >
                اقرأ المزيد

                <span aria-hidden="true">
                    ←
                </span>
            </a>
        @endif

    </div>

</article>