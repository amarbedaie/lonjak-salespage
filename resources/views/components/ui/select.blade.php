@props([])
<div class="relative">
    <select {{ $attributes->merge(['class' => 'w-full h-10 pl-3 pr-9 rounded-[var(--radius-md)] border border-border bg-bg text-ink text-sm appearance-none cursor-pointer transition-[border-color,box-shadow] duration-150 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/25']) }}>
        {{ $slot }}
    </select>
    <x-lucide-chevron-down class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-muted" />
</div>
