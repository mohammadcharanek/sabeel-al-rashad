@props([
    'value',
    'label',
    'icon' => 'users',
])

<div
    {{ $attributes->class([
        'flex min-h-[164px] flex-col items-center justify-center text-center lg:min-h-[150px]'
    ]) }}
>

    <div class="mb-3 text-brand-gold opacity-80">

        @switch($icon)

            @case('teachers')
                <svg
                    viewBox="0 0 24 24"
                    class="h-[26px] w-[26px]"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    aria-hidden="true"
                >
                    <path d="M4 19c1.5-3 4-4.5 8-4.5S18.5 16 20 19" />
                    <circle cx="12" cy="8" r="3.5" />
                    <path d="M8 5.5 12 3l4 2.5" />
                </svg>
                @break

            @case('history')
                <svg
                    viewBox="0 0 24 24"
                    class="h-[26px] w-[26px]"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    aria-hidden="true"
                >
                    <circle cx="12" cy="12" r="7" />
                    <path d="M12 7v5l3 2" />
                </svg>
                @break

            @case('activity')
                <svg
                    viewBox="0 0 24 24"
                    class="h-[26px] w-[26px]"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    aria-hidden="true"
                >
                    <path d="M3 12h4l2-6 4 12 2-6h6" />
                </svg>
                @break

            @default
                <svg
                    viewBox="0 0 24 24"
                    class="h-[26px] w-[26px]"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    aria-hidden="true"
                >
                    <circle cx="9" cy="8" r="3" />
                    <circle cx="16" cy="9" r="2.5" />
                    <path d="M3.5 19c.8-4 3-6 6-6s5.2 2 6 6" />
                    <path d="M15 14c3 0 4.7 1.8 5.5 5" />
                </svg>
        @endswitch

    </div>

    <div
        class="text-[34px] font-bold
               leading-none text-brand-gold"
    >
        {{ $value }}
    </div>

    <div
        class="mt-3 text-[14px]
               text-white/70"
    >
        {{ $label }}
    </div>

</div>