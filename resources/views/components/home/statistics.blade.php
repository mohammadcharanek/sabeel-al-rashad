@props(['statistics'])

<section id="statistics" class="bg-brand-navy py-12 md:py-16" aria-label="إحصائيات المدرسة">
    <div class="mx-auto max-w-[1200px] px-4 md:px-8">
        @if ($statistics->isNotEmpty())
            <div class="grid grid-cols-2 gap-y-5 md:grid-cols-4 md:gap-x-4 lg:flex lg:justify-between">
                @foreach ($statistics as $statistic)
                    <x-home.statistic-item
                        :value="$statistic->value"
                        :label="$statistic->label"
                        :icon="$statistic->icon ?? 'users'"
                        class="mx-auto w-full max-w-[160px] lg:max-w-[190px]"
                    />
                @endforeach
            </div>
        @else
            <p class="text-center text-sm leading-8 text-white/80">
                ستُعرض إحصائيات المدرسة هنا بعد اعتماد البيانات الرسمية.
            </p>
        @endif
    </div>
</section>
