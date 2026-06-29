@props(['label' => null, 'hint' => null, 'for' => null])
<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label @if ($for) for="{{ $for }}" @endif class="flex items-center justify-between text-sm font-medium text-ink">
            <span>{{ $label }}</span>
            @if ($hint)<span class="text-xs font-normal text-muted">{{ $hint }}</span>@endif
        </label>
    @endif
    {{ $slot }}
</div>
