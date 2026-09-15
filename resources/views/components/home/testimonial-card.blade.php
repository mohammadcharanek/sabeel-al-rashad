@props([
    'quote',
    'name' => 'ولي أمر',
    'role' => 'ولي أمر طالب',
    'avatar' => null,
])

<article
    {{ $attributes->class([
        'flex min-h-[220px] flex-col rounded-[14px]',
        'border border-[#E5E7EB] bg-white p-[23px]',
        'text-right',
        'transition duration-300',
        'hover:-translate-y-1 hover:shadow-[0_16px_36px_rgba(14,35,64,0.08)]',
    ]) }}
>
    {{-- Rating --}}
    <div
        class="font-latin text-[16px]
               font-semibold tracking-[2px]
               text-brand-gold"
        aria-label="5 من 5 نجوم"
    >
        ★★★★★
    </div>

    {{-- Quote --}}
    <blockquote
        class="mt-4 flex-1
               text-[16px] leading-8
               text-text-primary"
    >
        “{{ $quote }}”
    </blockquote>

    {{-- Parent --}}
    <div class="mt-5 flex items-center gap-3">

        <div
            class="flex h-[42px] w-[42px]
                   shrink-0 items-center justify-center
                   overflow-hidden rounded-full
                   bg-[#F7EAD1]"
        >
            @if ($avatar && file_exists(public_path($avatar)))
                <img
                    src="{{ asset($avatar) }}"
                    alt=""
                    loading="lazy"
                    class="h-full w-full object-cover"
                >
            @else
                <svg
                    viewBox="0 0 24 24"
                    class="h-5 w-5 text-brand-gold-dark/60"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    aria-hidden="true"
                >
                    <circle cx="12" cy="8" r="3.5" />
                    <path d="M5 20c1-4.5 3.4-7 7-7s6 2.5 7 7" />
                </svg>
            @endif
        </div>

        <div>
            <h3
                class="text-[14px]
                       font-semibold
                       text-brand-navy"
            >
                {{ $name }}
            </h3>

            <p
                class="mt-0.5 text-[12px]
                       text-text-muted"
            >
                {{ $role }}
            </p>
        </div>

    </div>
</article>