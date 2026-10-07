@props([
    'number',
    'title',
    'description' => null,
    'icon' => 'basic',
    'href' => null,
    'linkLabel' => 'تعرف أكثر',
])

<article
    {{ $attributes->class([
        'group relative min-h-[310px] overflow-hidden rounded-[14px]',
        'border border-[#E5E7EB] bg-white',
        'px-[27px] pb-7 pt-[38px]',
        'transition duration-300',
        'hover:-translate-y-1 hover:shadow-[0_16px_36px_rgba(14,35,64,0.10)]',
        'md:min-h-[318px]',
    ]) }}
>
    {{-- Gold top accent --}}
    <div
        class="absolute inset-x-0 top-0 h-1 bg-brand-gold"
        aria-hidden="true"
    ></div>

    {{-- Number --}}
    <span
        class="absolute left-[27px] top-[25px]
               text-[40px] font-bold leading-none
               text-text-muted"
    >
        {{ $number }}
    </span>

    {{-- Icon --}}
    <div
        class="absolute right-[38px] top-[38px]
               text-brand-navy"
        aria-hidden="true"
    >
        @switch($icon)

            @case('kindergarten')
            @case('Kindergarten Stage')
                <svg
                    viewBox="0 0 32 32"
                    class="h-8 w-8"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <circle cx="16" cy="10" r="4" />
                    <path d="M9 26c0-5 2.5-9 7-9s7 4 7 9" />
                    <path d="M6 12h3" />
                    <path d="M7.5 10.5v3" />
                    <path d="M23 12h3" />
                    <path d="M24.5 10.5v3" />
                </svg>
                @break

            @case('middle')
            @case('intermediate')
            @case('middle_school')
                <svg
                    viewBox="0 0 32 32"
                    class="h-8 w-8"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <rect x="6" y="7" width="20" height="18" rx="2" />
                    <path d="M10 11h12" />
                    <path d="M10 15h5" />
                    <path d="M10 20h12" />
                    <circle cx="22" cy="15" r="1" />
                </svg>
                @break

            @case('secondary')
            @case('high_school')
                <svg
                    viewBox="0 0 32 32"
                    class="h-8 w-8"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <circle cx="15" cy="10" r="4" />
                    <path d="M8 26c0-6 2.5-10 7-10s7 4 7 10" />
                    <path d="M23 8h4" />
                    <path d="M25 6v4" />
                </svg>
                @break

            @case('elementary')
            @case('primary')
            @case('basic')
            @case('american_elementary')
            @case('Basic Education Stage')
                <svg
                    viewBox="0 0 32 32"
                    class="h-8 w-8"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <path d="M16 5 26 10 16 15 6 10 16 5Z" />
                    <path d="M6 15 16 20 26 15" />
                    <path d="M6 20 16 25 26 20" />
                </svg>
                @break

            @default
                <svg
                    viewBox="0 0 32 32"
                    class="h-8 w-8"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <path d="M16 5 26 10 16 15 6 10 16 5Z" />
                    <path d="M6 15 16 20 26 15" />
                    <path d="M6 20 16 25 26 20" />
                </svg>

        @endswitch
    </div>

    <div class="mt-[68px] text-right">

        <h3
            class="text-[20px] font-bold
                   leading-8 text-brand-navy
                   md:text-[22px]"
        >
            {{ $title }}
        </h3>

        @if ($description)
            <p
                class="mt-3 min-h-[72px]
                       text-[15px] leading-7
                       text-text-muted"
            >
                {{ $description }}
            </p>
        @endif

        @if ($href)
            <a
                href="{{ $href }}"
                class="mt-5 inline-flex min-h-11 min-w-11 items-center
                       text-[14px] font-semibold
                       text-text-gold
                       transition
                       group-hover:text-brand-navy"
            >
                {{ $linkLabel }}
            </a>
        @endif

    </div>
</article>
