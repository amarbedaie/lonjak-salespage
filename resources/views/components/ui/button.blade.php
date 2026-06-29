@props(['variant' => 'primary', 'size' => 'md', 'href' => null])
@php
$base = 'inline-flex items-center justify-center gap-2 font-medium rounded-[var(--radius-md)] transition-[background,color,border-color,box-shadow,transform] duration-150 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring disabled:opacity-50 disabled:pointer-events-none active:scale-[0.98] select-none whitespace-nowrap';
$variants = [
    'primary'   => 'bg-primary text-primary-fg hover:bg-primary-hover active:bg-primary-active shadow-[0_1px_2px_rgba(0,0,0,0.08)]',
    'secondary' => 'bg-muted-surface text-ink hover:bg-border/70 border border-border',
    'outline'   => 'bg-transparent text-ink border border-border-strong hover:bg-muted-surface',
    'ghost'     => 'bg-transparent text-ink-soft hover:bg-muted-surface hover:text-ink',
    'danger'    => 'bg-danger text-white hover:opacity-90',
];
$sizes = ['sm' => 'h-8 px-3 text-[0.8125rem]', 'md' => 'h-9 px-4 text-sm', 'lg' => 'h-11 px-6 text-[0.9375rem]', 'icon' => 'h-9 w-9'];
$cls = $base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $cls]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $cls]) }}>{{ $slot }}</button>
@endif
