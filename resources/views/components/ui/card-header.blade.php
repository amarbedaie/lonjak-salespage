@props(['title' => null, 'subtitle' => null])
<div {{ $attributes->merge(['class' => 'flex items-start justify-between gap-3 px-5 py-4 border-b border-border']) }}>
    <div class="min-w-0">
        @if ($title)<h3 class="text-sm font-semibold text-ink">{{ $title }}</h3>@endif
        @if ($subtitle)<p class="mt-0.5 text-xs text-muted">{{ $subtitle }}</p>@endif
    </div>
    @isset($action){{ $action }}@endisset
</div>
