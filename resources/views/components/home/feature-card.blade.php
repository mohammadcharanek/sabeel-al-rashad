@props([
    'title',
    'description',
    'icon' => 'academic',
])

<article
    {{ $attributes->class([
        'mx-auto flex h-[188px] w-full max-w-[326px] flex-col',
        'rounded-2xl border border-[#DDE4ED]',
        'bg-[#F4F7FB] p-7 text-right',
        'transition duration-300',
        'hover:-translate-y-1 hover:shadow-[0_14px_32px_rgba(14,35,64,0.08)]',
        'md:h-[203px] md:max-w-none',
    ]) }}
>

    <div
        class="flex h-12 w-12 shrink-0
               items-center justify-center
               rounded-xl bg-brand-navy/5
               text-brand-navy"
        aria-hidden="true"
    >

        @switch($icon)

            @case('safe')
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none"
                     stroke="currentColor" stroke-width="1.6">
                    <path d="M12 3 18 5.5V11c0 4.3-2.4 7.4-6 9-3.6-1.6-6-4.7-6-9V5.5L12 3Z"/>
                </svg>
                @break

            @case('staff')
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none"
                     stroke="currentColor" stroke-width="1.6">
                    <circle cx="9" cy="8" r="3"/>
                    <circle cx="16.5" cy="9" r="2.4"/>
                    <path d="M3.5 19c.8-4 3-6 5.8-6s5 2 5.8 6"/>
                    <path d="M15 14c3 0 4.8 1.8 5.5 5"/>
                </svg>
                @break

            @case('values')
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none"
                     stroke="currentColor" stroke-width="1.6">
                    <path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10Z"/>
                </svg>
                @break

            @case('activities')
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none"
                     stroke="currentColor" stroke-width="1.6">
                    <path d="m12 3 2.1 4.6 5 .6-3.7 3.5 1 5-4.4-2.4-4.4 2.4 1-5L5 8.2l5-.6L12 3Z"/>
                </svg>
                @break

            @case('followup')
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none"
                     stroke="currentColor" stroke-width="1.6">
                    <path d="M2.5 12s3.3-5 9.5-5 9.5 5 9.5 5-3.3 5-9.5 5-9.5-5-9.5-5Z"/>
                    <circle cx="12" cy="12" r="2.7"/>
                </svg>
                @break

            @default
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none"
                     stroke="currentColor" stroke-width="1.6">
                    <path d="m12 4 8 4-8 4-8-4 8-4Z"/>
                    <path d="M7 10.5V15c2.8 2.1 7.2 2.1 10 0v-4.5"/>
                </svg>

        @endswitch

    </div>

    <h3
        class="mt-4 text-[16px]
               font-black leading-6
               text-brand-navy"
    >
        {{ $title }}
    </h3>

    <p
        class="mt-2 text-[14px]
               leading-[24.5px]
               text-[#607080]"
    >
        {{ $description }}
    </p>

</article>