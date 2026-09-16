@props(['siteSettings'])

@if ($whatsappUrl = $siteSettings->whatsappUrl())
    <a
        href="{{ $whatsappUrl }}"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="تواصل مع المدرسة عبر واتساب (يفتح في نافذة جديدة)"
        {{ $attributes->class(['inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-brand-gold/50 px-4 py-3 text-sm font-bold text-brand-gold transition hover:bg-brand-gold hover:text-brand-navy-dark']) }}
    >
        <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a9 9 0 0 1-13.4 7.9L3 21l1.6-4.6A9 9 0 1 1 21 11.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7.5c0 4.7 3.8 8.5 8.5 8.5l1-2.5-3-1-1 1a8 8 0 0 1-3-3l1-1-1-3L8 7.5Z" />
        </svg>
        تواصل عبر واتساب
    </a>
@endif
