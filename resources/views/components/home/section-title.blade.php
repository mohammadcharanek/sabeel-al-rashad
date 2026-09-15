@props([
    'eyebrow',
    'title',
    'description' => null,
    'dark' => false,
])

<div class="mx-auto max-w-[760px] text-center">

    <span
        class="inline-flex min-h-[28px] items-center justify-center
               rounded-full bg-[#F7EAD1]
               px-4 py-1
               text-[13px] font-semibold
               text-brand-gold-dark"
    >
        {{ $eyebrow }}
    </span>

    <h2
        @class([
            'mt-3 text-[28px] font-bold leading-tight md:text-[32px]',
            'text-white' => $dark,
            'text-brand-navy' => ! $dark,
        ])
    >
        {{ $title }}
    </h2>

    @if ($description)
        <p
            @class([
                'mx-auto mt-3 max-w-[720px] text-[15px] leading-7 md:text-[16px]',
                'text-white/60' => $dark,
                'text-text-muted' => ! $dark,
            ])
        >
            {{ $description }}
        </p>
    @endif

</div>