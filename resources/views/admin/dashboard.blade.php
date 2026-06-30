<x-layouts.admin title="Admin">
    @php $rm = fn ($n, $c = false) => $c && $n >= 1000 ? 'RM'.number_format($n / 1000, 1).'k' : 'RM'.number_format($n, 2); @endphp
    <div class="space-y-6">
        <x-ui.page-header title="Ringkasan platform" description="Prestasi seluruh Mendap." />
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-ui.metric label="Merchant" :value="number_format($stats['merchants'])" />
            <x-ui.metric label="MRR (anggaran)" :value="$rm($stats['mrr'], true)" :footnote="$stats['merchants'].' × RM89'" />
            <x-ui.metric label="GMV platform" :value="$rm($stats['gmv'], true)" />
            <x-ui.metric label="Jumlah order" :value="number_format($stats['orders'])" />
        </div>
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-ui.metric label="Salespage" :value="number_format($stats['salespages'])" />
            <x-ui.metric label="Salespage live" :value="number_format($stats['liveSalespages'])" />
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-ui.card>
                <x-ui.card-header title="Order terkini (semua merchant)"><x-slot:action><a href="{{ route('admin.orders') }}" class="text-xs font-medium text-primary hover:underline">Semua</a></x-slot:action></x-ui.card-header>
                <x-ui.card-body class="p-0">
                    @forelse ($recentOrders as $o)
                        <div class="flex items-center gap-3 border-b border-border px-5 py-3 last:border-0">
                            <div class="min-w-0 flex-1"><p class="truncate text-sm font-medium text-ink">{{ $o->customer }}</p><p class="text-xs text-muted">{{ $o->product_name }} · {{ $o->created_at->diffForHumans() }}</p></div>
                            <x-ui.status-pill :status="$o->status" /><span class="text-sm font-semibold text-ink tnum">{{ $rm($o->total) }}</span>
                        </div>
                    @empty<p class="px-5 py-8 text-center text-sm text-muted">Belum ada order.</p>@endforelse
                </x-ui.card-body>
            </x-ui.card>
            <x-ui.card>
                <x-ui.card-header title="Merchant terkini"><x-slot:action><a href="{{ route('admin.merchants') }}" class="text-xs font-medium text-primary hover:underline">Semua</a></x-slot:action></x-ui.card-header>
                <x-ui.card-body class="p-0">
                    @forelse ($recentMerchants as $m)
                        <a href="{{ route('admin.merchant', $m) }}" class="flex items-center gap-3 border-b border-border px-5 py-3 last:border-0 hover:bg-muted-surface/50">
                            <span class="flex size-8 items-center justify-center rounded-full bg-primary text-xs font-semibold text-primary-fg">{{ strtoupper(substr($m->business_name ?: $m->email, 0, 1)) }}</span>
                            <div class="min-w-0 flex-1"><p class="truncate text-sm font-medium text-ink">{{ $m->business_name ?: '—' }}</p><p class="truncate text-xs text-muted">{{ $m->email }}</p></div>
                            <x-ui.status-pill :status="$m->status" />
                        </a>
                    @empty<p class="px-5 py-8 text-center text-sm text-muted">Belum ada merchant.</p>@endforelse
                </x-ui.card-body>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
