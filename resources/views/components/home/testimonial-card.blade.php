@props([
    'quote',
    'name',
    'role' => null,
])

<figure
    {{ $attributes->class([
        'flex h-full min-w-0 flex-col gap-6 rounded-2xl',
        'border border-border-soft border-t-4 border-t-brand-gold bg-white p-6 md:p-7',
        'text-right shadow-sm',
    ]) }}
>
    <blockquote class="flex-1 whitespace-pre-line text-base leading-8 wrap-anywhere text-text-primary">{{ $quote }}</blockquote>

    <figcaption class="border-t border-border-soft pt-5">
        <p class="text-sm font-bold wrap-anywhere text-brand-navy">{{ $name }}</p>
        @if (filled($role))
            <p class="mt-1 text-xs leading-6 wrap-anywhere text-text-muted">{{ $role }}</p>
        @endif
    </figcaption>
</figure>
