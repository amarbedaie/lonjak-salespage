@props([])
<div {{ $attributes->merge(['class' => 'rounded-[var(--radius-lg)] border border-border bg-surface']) }}>{{ $slot }}</div>
