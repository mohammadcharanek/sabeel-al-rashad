@props(['name', 'label', 'required' => false, 'type' => 'text'])

<div class="min-w-0">
    <label for="{{ $name }}" class="mb-2 block text-sm font-bold text-brand-navy">
        {{ $label }} <span class="font-normal text-text-muted">({{ $required ? 'مطلوب' : 'اختياري' }})</span>
    </label>
    @if ($slot->isNotEmpty())
        {{ $slot }}
    @else
        <input
            id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
            @if ($type !== 'file') value="{{ is_scalar(old($name)) ? old($name) : '' }}" @endif
            @required($required)
            aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
            @if ($errors->has($name) && ! $attributes->has('aria-describedby')) aria-describedby="{{ $name }}-error" @endif
            {{ $attributes->class(['block min-h-12 w-full min-w-0 rounded-lg border border-border-card bg-white px-3 py-3 text-base text-text-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy']) }}
        >
    @endif
    @error($name)
        <p id="{{ $name }}-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
    @enderror
</div>
