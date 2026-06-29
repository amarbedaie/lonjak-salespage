@props(['icon' => 'sparkles', 'title', 'description', 'actionLabel' => null, 'actionHref' => null])
<div class="flex flex-col items-center justify-center rounded-[var(--radius-lg)] border border-dashed border-border-strong bg-surface px-6 py-16 text-center">
    <div class="mb-4 flex size-12 items-center justify-center rounded-full bg-primary-soft text-primary">
        <x-lucide-{{ $icon }} class="size-5" />
    </div>
    <h3 class="text-base font-semibold text-ink">{{ $title }}</h3>
    <p class="mt-1 max-w-sm text-sm text-muted">{{ $description }}</p>
    @if ($actionLabel && $actionHref)
        <x-ui.button :href="$actionHref" class="mt-5">{{ $actionLabel }}</x-ui.button>
    @endif
</div>
