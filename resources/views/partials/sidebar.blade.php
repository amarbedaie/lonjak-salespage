<div class="flex h-full flex-col">
    <div class="flex h-16 items-center px-5">
        <a href="{{ route('dashboard') }}"><x-logo /></a>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto scroll-thin px-3 pb-4">
        <ul class="space-y-0.5">
            @foreach ($nav as [$label, $route, $icon, $isActive])
                @php $active = $isActive(); @endphp
                <li><a href="{{ route($route) }}" class="{{ $itemCls($active) }}">
                    @if ($active)<span class="absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-r-full bg-primary"></span>@endif
                    <x-dynamic-component :component="'lucide-'.$icon" class="size-4.5 shrink-0 {{ $active ? 'text-primary' : 'text-muted group-hover:text-ink' }}" />
                    <span class="flex-1">{{ $label }}</span>
                </a></li>
            @endforeach
        </ul>

        <div>
            <p class="px-3 pb-1.5 text-[0.6875rem] font-semibold uppercase tracking-wider text-muted">Jualan</p>
            <ul class="space-y-0.5">
                @foreach ($jualan as [$label, $route, $icon, $isActive])
                    @php $active = $isActive(); @endphp
                    <li><a href="{{ route($route) }}" class="{{ $itemCls($active) }}">
                        @if ($active)<span class="absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-r-full bg-primary"></span>@endif
                        <x-dynamic-component :component="'lucide-'.$icon" class="size-4.5 shrink-0 {{ $active ? 'text-primary' : 'text-muted group-hover:text-ink' }}" />
                        <span class="flex-1">{{ $label }}</span>
                    </a></li>
                @endforeach
            </ul>
        </div>

        <div>
            <p class="px-3 pb-1.5 text-[0.6875rem] font-semibold uppercase tracking-wider text-muted">Operasi</p>
            <ul class="space-y-0.5">
                @foreach ($operasi as [$label, $route, $icon, $isActive])
                    @php $active = $isActive(); @endphp
                    <li><a href="{{ route($route) }}" class="{{ $itemCls($active) }}">
                        @if ($active)<span class="absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-r-full bg-primary"></span>@endif
                        <x-dynamic-component :component="'lucide-'.$icon" class="size-4.5 shrink-0 {{ $active ? 'text-primary' : 'text-muted group-hover:text-ink' }}" />
                        <span class="flex-1">{{ $label }}</span>
                    </a></li>
                @endforeach
            </ul>
        </div>

        <ul class="space-y-0.5">
            <li><a href="{{ route('settings') }}" class="{{ $itemCls(request()->is('dashboard/settings')) }}">
                @if (request()->is('dashboard/settings'))<span class="absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-r-full bg-primary"></span>@endif
                <x-lucide-settings class="size-4.5 shrink-0 {{ request()->is('dashboard/settings') ? 'text-primary' : 'text-muted group-hover:text-ink' }}" />
                <span class="flex-1">Tetapan</span>
            </a></li>
        </ul>

        @if ($user->isAdmin())
            <div>
                <p class="px-3 pb-1.5 text-[0.6875rem] font-semibold uppercase tracking-wider text-muted">Platform</p>
                <a href="{{ route('admin.dashboard') }}" class="{{ $itemCls(request()->is('admin*')) }}">
                    <x-lucide-shield class="size-4.5 shrink-0 text-muted group-hover:text-ink" />
                    <span class="flex-1">Admin</span>
                    <x-ui.badge tone="warning" class="px-1.5 py-0">super</x-ui.badge>
                </a>
            </div>
        @endif
    </nav>

    <div class="px-3 pb-4">
        <div class="rounded-[var(--radius-lg)] border border-border bg-gradient-to-b from-primary-soft to-surface p-4">
            <div class="flex items-center gap-2 text-primary">
                <x-lucide-sparkles class="size-4" />
                <span class="text-xs font-semibold">Baki kredit AI</span>
            </div>
            <p class="mt-1.5 text-sm text-ink"><span class="text-lg font-semibold tnum">{{ $user->ai_credits }}</span><span class="text-muted"> / 3 generasi</span></p>
            <a href="{{ route('settings') }}" class="mt-2 inline-block text-xs font-medium text-primary hover:underline">Tambah kredit →</a>
        </div>
    </div>
</div>
