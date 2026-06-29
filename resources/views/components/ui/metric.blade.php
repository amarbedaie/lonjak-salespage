@props(['label', 'value', 'delta' => null, 'footnote' => null])
<div class="rounded-[var(--radius-lg)] border border-border bg-surface p-4">
    <div class="flex items-start justify-between gap-2">
        <span class="text-[0.8125rem] font-medium text-muted">{{ $label }}</span>
        @if (! is_null($delta))
            @php $up = $delta >= 0; @endphp
            <span class="inline-flex items-center gap-0.5 text-xs font-medium {{ $up ? 'text-success' : 'text-danger' }}">
                <x-lucide-{{ $up ? 'trending-up' : 'trending-down' }} class="size-3.5" />{{ abs($delta) }}%
            </span>
        @endif
    </div>
    <div class="mt-1.5 text-2xl font-semibold tracking-tight text-ink tnum">{{ $value }}</div>
    @if ($footnote)<p class="mt-1 text-xs text-muted">{{ $footnote }}</p>@endif
</div>
