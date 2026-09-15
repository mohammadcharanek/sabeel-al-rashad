@props([
    'src',
    'alt',
])

<div {{ $attributes->class(['group relative overflow-hidden rounded-xl bg-white/5']) }}>

    @if (file_exists(public_path($src)))
        <img
            src="{{ asset($src) }}"
            alt="{{ $alt }}"
            loading="lazy"
            class="h-full w-full object-cover
                   transition duration-500
                   group-hover:scale-[1.03]"
        >
    @else
        <div
            class="flex h-full w-full items-center justify-center
                   bg-white/[0.04]
                   px-4 text-center
                   text-sm text-white/40"
        >
            {{ $alt }}
        </div>
    @endif

</div>