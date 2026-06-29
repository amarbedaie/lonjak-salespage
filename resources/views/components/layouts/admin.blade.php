@props(['title' => null])
@php
    $items = [
        ['Ringkasan', 'admin.dashboard', 'layout-dashboard', fn () => request()->is('admin')],
        ['Merchant', 'admin.merchants', 'users', fn () => request()->is('admin/merchants*')],
        ['Salespage', 'admin.salespages', 'file-text', fn () => request()->is('admin/salespages*')],
        ['Order', 'admin.orders', 'shopping-cart', fn () => request()->is('admin/orders*')],
    ];
    $itemCls = fn ($a) => 'relative flex items-center gap-3 rounded-[var(--radius-md)] px-3 py-2 text-sm font-medium transition-colors '
        .($a ? 'bg-primary-soft text-primary' : 'text-ink-soft hover:bg-muted-surface hover:text-ink');
@endphp
<x-layouts.base :title="$title">
<div x-data="{ mobile: false }" class="flex min-h-screen bg-bg">
    <aside class="sticky top-0 hidden h-screen w-64 shrink-0 border-r border-border bg-panel lg:block">
        <div class="flex h-full flex-col">
            <div class="flex h-16 items-center gap-2 px-5">
                <a href="{{ route('admin.dashboard') }}"><x-logo /></a>
                <x-ui.badge tone="warning">Admin</x-ui.badge>
            </div>
            <nav class="flex-1 space-y-0.5 px-3">
                @foreach ($items as [$label, $route, $icon, $isActive])
                    @php $a = $isActive(); @endphp
                    <a href="{{ route($route) }}" class="{{ $itemCls($a) }}">
                        @if ($a)<span class="absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-r-full bg-primary"></span>@endif
                        <x-dynamic-component :component="'lucide-'.$icon" class="size-4.5 {{ $a ? 'text-primary' : 'text-muted' }}" />{{ $label }}
                    </a>
                @endforeach
            </nav>
            <div class="px-3 pb-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 rounded-[var(--radius-md)] px-3 py-2 text-sm font-medium text-ink-soft hover:bg-muted-surface hover:text-ink">
                    <x-lucide-arrow-left class="size-4" /> Dashboard merchant
                </a>
            </div>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-[1100] flex h-16 items-center gap-3 border-b border-border bg-bg/80 px-4 backdrop-blur-md sm:px-6">
            <span class="flex items-center gap-2 text-sm font-medium text-ink-soft"><x-lucide-shield class="size-4 text-primary" /> Panel Admin Platform</span>
            <div class="ml-auto flex items-center gap-2">
                <x-theme-toggle />
                <span class="hidden text-xs text-muted sm:block">{{ auth()->user()->business_name }}</span>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" class="inline-flex size-9 items-center justify-center rounded-[var(--radius-md)] border border-border bg-bg text-ink-soft hover:text-danger"><x-lucide-log-out class="size-4.5" /></button>
                </form>
            </div>
        </header>
        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8"><div class="mx-auto w-full max-w-7xl animate-in">{{ $slot }}</div></main>
    </div>
</div>
</x-layouts.base>
