@props(['text' => true])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
    <span class="relative inline-flex size-7 items-center justify-center rounded-[8px] bg-primary text-primary-fg shadow-[0_1px_3px_rgba(0,0,0,0.12)]">
        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M4 17 9 11l3.5 3.5L20 6" />
            <path d="M15 6h5v5" />
        </svg>
    </span>
    @if ($text)<span class="text-[1.0625rem] font-semibold tracking-tight text-ink">Lonjak</span>@endif
</span>
