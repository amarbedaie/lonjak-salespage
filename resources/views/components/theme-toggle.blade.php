@props([])
<button type="button"
    x-data="{ dark: document.documentElement.classList.contains('dark') }"
    @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('mendap-theme', dark ? 'dark' : 'light')"
    title="Tukar tema"
    {{ $attributes->merge(['class' => 'inline-flex size-9 items-center justify-center rounded-[var(--radius-md)] border border-border bg-bg text-ink-soft transition-colors hover:text-ink hover:bg-muted-surface']) }}>
    <x-lucide-moon class="size-4.5" x-show="!dark" x-cloak />
    <x-lucide-sun class="size-4.5" x-show="dark" x-cloak />
</button>
