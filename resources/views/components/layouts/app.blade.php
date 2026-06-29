@props(['title' => null])
@php
    $user = auth()->user();
    $nav = [
        ['Ringkasan', 'dashboard', 'layout-dashboard', fn () => request()->is('dashboard')],
    ];
    $jualan = [
        ['Salespage', 'salespages.index', 'file-text', fn () => request()->is('dashboard/salespages*')],
        ['Order', 'orders.index', 'shopping-cart', fn () => request()->is('dashboard/orders*')],
        ['Produk', 'products.index', 'package', fn () => request()->is('dashboard/products*')],
        ['Analitik', 'analytics', 'bar-chart-3', fn () => request()->is('dashboard/analytics')],
    ];
    $operasi = [
        ['Bayaran', 'payments', 'credit-card', fn () => request()->is('dashboard/payments')],
        ['Penghantaran', 'shipping', 'truck', fn () => request()->is('dashboard/shipping')],
        ['Recovery', 'recovery', 'message-circle-heart', fn () => request()->is('dashboard/recovery')],
        ['Affiliate', 'affiliate', 'users', fn () => request()->is('dashboard/affiliate')],
    ];
    $itemCls = fn ($active) => 'group relative flex items-center gap-3 rounded-[var(--radius-md)] px-3 py-2 text-sm font-medium transition-colors '
        .($active ? 'bg-primary-soft text-primary' : 'text-ink-soft hover:bg-muted-surface hover:text-ink');
@endphp

<x-layouts.base :title="$title">
<div x-data="{ mobile: false }" class="flex min-h-screen bg-bg">
    {{-- Sidebar (desktop) --}}
    <aside class="sticky top-0 hidden h-screen w-64 shrink-0 border-r border-border bg-panel lg:block">
        @include('partials.sidebar', compact('nav', 'jualan', 'operasi', 'user', 'itemCls'))
    </aside>

    {{-- Mobile sidebar --}}
    <div x-show="mobile" x-cloak class="fixed inset-0 z-[1200] lg:hidden">
        <div @click="mobile=false" x-show="mobile" x-transition.opacity class="absolute inset-0 bg-black/40 backdrop-blur-[2px]"></div>
        <aside x-show="mobile" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
               class="absolute left-0 top-0 h-full w-72 border-r border-border bg-panel">
            <button @click="mobile=false" class="absolute right-3 top-5 inline-flex size-8 items-center justify-center rounded-md text-muted hover:bg-muted-surface hover:text-ink">
                <x-lucide-x class="size-4.5" />
            </button>
            @include('partials.sidebar', compact('nav', 'jualan', 'operasi', 'user', 'itemCls'))
        </aside>
    </div>

    <div class="flex min-w-0 flex-1 flex-col">
        {{-- Topbar --}}
        <header class="sticky top-0 z-[1100] flex h-16 items-center gap-3 border-b border-border bg-bg/80 px-4 backdrop-blur-md sm:px-6">
            <button @click="mobile=true" class="inline-flex size-9 items-center justify-center rounded-md text-ink-soft hover:bg-muted-surface lg:hidden">
                <x-lucide-menu class="size-5" />
            </button>
            <div class="relative hidden max-w-md flex-1 sm:block">
                <x-lucide-search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted" />
                <input type="search" placeholder="Cari order, produk, pelanggan…"
                    class="h-9 w-full rounded-[var(--radius-md)] border border-border bg-muted-surface pl-9 pr-4 text-sm text-ink placeholder:text-muted focus:border-primary focus:bg-bg focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>
            <div class="ml-auto flex items-center gap-2">
                <x-ui.button href="{{ route('salespages.create') }}" size="sm" class="hidden sm:inline-flex">
                    <x-lucide-plus class="size-4" /> Salespage baru
                </x-ui.button>
                <x-theme-toggle />
                <a href="{{ route('settings') }}" class="flex items-center gap-2 rounded-[var(--radius-md)] py-1 pl-1 pr-2 transition-colors hover:bg-muted-surface">
                    <span class="flex size-8 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-fg">{{ strtoupper(substr($user->business_name ?: $user->email, 0, 1)) }}</span>
                    <span class="hidden text-left leading-tight md:block">
                        <span class="block text-xs font-medium text-ink">{{ $user->business_name ?: 'Pengguna' }}</span>
                        <span class="block text-[0.6875rem] text-muted">{{ $user->email }}</span>
                    </span>
                </a>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" title="Log keluar" class="inline-flex size-9 items-center justify-center rounded-[var(--radius-md)] border border-border bg-bg text-ink-soft transition-colors hover:bg-muted-surface hover:text-danger">
                        <x-lucide-log-out class="size-4.5" />
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-7xl animate-in">{{ $slot }}</div>
        </main>
    </div>
</div>
</x-layouts.base>
