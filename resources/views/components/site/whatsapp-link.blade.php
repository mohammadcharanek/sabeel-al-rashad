@props(['siteSettings'])

@if ($whatsappUrl = $siteSettings->whatsappUrl())
    <div @class(['h-[calc(5.5rem+env(safe-area-inset-bottom))] bg-[#07111E]', 'flex items-center justify-center' => request()->routeIs('registration.*')])>
        <a
            data-whatsapp-contact
            href="{{ $whatsappUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="تواصل مع المدرسة عبر واتساب (يفتح في نافذة جديدة)"
            {{ $attributes->class([
                'z-40 inline-flex min-h-14 min-w-14 items-center justify-center gap-2 rounded-full border border-white/30 bg-green-800 px-4 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-green-900',
                'fixed bottom-[calc(1rem+env(safe-area-inset-bottom))] left-[max(1rem,env(safe-area-inset-left))]' => ! request()->routeIs('registration.*'),
            ]) }}
        >
            <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a9 9 0 0 1-13.4 7.9L3 21l1.6-4.6A9 9 0 1 1 21 11.5Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7.5c0 4.7 3.8 8.5 8.5 8.5l1-2.5-3-1-1 1a8 8 0 0 1-3-3l1-1-1-3L8 7.5Z" />
            </svg>
            <span><span class="hidden sm:inline">تواصل عبر </span>واتساب</span>
        </a>
    </div>
@endif
