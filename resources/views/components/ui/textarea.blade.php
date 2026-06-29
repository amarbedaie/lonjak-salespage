@props([])
<textarea {{ $attributes->merge(['class' => 'w-full min-h-[88px] px-3 py-2 rounded-[var(--radius-md)] border border-border bg-bg text-ink text-sm placeholder:text-muted leading-relaxed resize-y transition-[border-color,box-shadow] duration-150 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/25']) }}>{{ $slot }}</textarea>
