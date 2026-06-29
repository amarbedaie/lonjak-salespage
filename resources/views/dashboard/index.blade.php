<x-layouts.app title="Ringkasan">
    @php $rm = fn ($n, $c = false) => $c && $n >= 1000 ? 'RM'.number_format($n / 1000, 1).'k' : 'RM'.number_format($n, 2); @endphp
    <div class="space-y-6">
        <x-ui.page-header title="Selamat datang, {{ $user->business_name ?: 'usahawan' }} 👋" description="Ringkasan prestasi kedai anda.">
            <x-slot:action>
                <x-ui.button href="{{ route('salespages.create') }}"><x-lucide-plus class="size-4" /> Salespage baru</x-ui.button>
            </x-slot:action>
        </x-ui.page-header>

        @if ($empty)
            <x-ui.empty-state icon="sparkles" title="Mari mula jual"
                description="Bina salespage pertama anda dengan AI, terbitkan, dan order akan masuk terus ke sini."
                actionLabel="Jana salespage pertama" :actionHref="route('salespages.create')" />
        @else
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <x-ui.metric label="Jumlah jualan" :value="$rm($overview['revenue'])" />
                <x-ui.metric label="Jumlah order" :value="number_format($overview['orders'])" />
                <x-ui.metric label="Salespage live" :value="$overview['liveSalespages']" />
                <x-ui.metric label="Jumlah pelawat" :value="number_format($overview['visitors'])" />
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <x-ui.card class="lg:col-span-2">
                    <x-ui.card-header title="Jualan" subtitle="14 hari lepas" />
                    <x-ui.card-body><x-ui.area-chart :data="$series" id="dash" /></x-ui.card-body>
                </x-ui.card>
                <x-ui.card>
                    <x-ui.card-header title="Salespage terbaik">
                        <x-slot:action><a href="{{ route('salespages.index') }}" class="text-xs font-medium text-primary hover:underline">Semua</a></x-slot:action>
                    </x-ui.card-header>
                    <x-ui.card-body class="p-0">
                        @forelse ($topPages as $s)
                            <div class="flex items-center gap-3 border-b border-border px-5 py-3 last:border-0">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-ink">{{ $s->title }}</p>
                                    <p class="text-xs text-muted">{{ number_format($s->visits) }} pelawat</p>
                                </div>
                                <span class="text-sm font-semibold text-ink tnum">{{ $rm($s->rev, true) }}</span>
                            </div>
                        @empty
                            <p class="px-5 py-8 text-center text-sm text-muted">Belum ada salespage.</p>
                        @endforelse
                    </x-ui.card-body>
                </x-ui.card>
            </div>

            <x-ui.card>
                <x-ui.card-header title="Order terkini">
                    <x-slot:action><a href="{{ route('orders.index') }}" class="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline">Semua order <x-lucide-arrow-up-right class="size-3.5" /></a></x-slot:action>
                </x-ui.card-header>
                <x-ui.card-body class="p-0">
                    @if ($recent->isEmpty())
                        <div class="px-5 py-10"><x-ui.empty-state icon="file-text" title="Belum ada order" description="Order pelanggan akan muncul di sini." /></div>
                    @else
                        <div class="overflow-x-auto scroll-thin">
                            <table class="w-full text-sm">
                                <thead><tr class="border-b border-border text-left text-xs text-muted">
                                    <th class="px-5 py-2.5 font-medium">Pelanggan</th><th class="px-5 py-2.5 font-medium">Produk</th>
                                    <th class="px-5 py-2.5 font-medium">Status</th><th class="px-5 py-2.5 text-right font-medium">Jumlah</th>
                                    <th class="px-5 py-2.5 text-right font-medium">Masa</th>
                                </tr></thead>
                                <tbody class="divide-y divide-border">
                                    @foreach ($recent as $o)
                                        <tr class="transition-colors hover:bg-muted-surface/60">
                                            <td class="px-5 py-3 font-medium text-ink">{{ $o->customer }}</td>
                                            <td class="px-5 py-3 text-ink-soft">{{ $o->product_name }}</td>
                                            <td class="px-5 py-3"><x-ui.status-pill :status="$o->status" /></td>
                                            <td class="px-5 py-3 text-right font-medium text-ink tnum">{{ $rm($o->total) }}</td>
                                            <td class="px-5 py-3 text-right text-xs text-muted">{{ $o->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-ui.card-body>
            </x-ui.card>
        @endif
    </div>
</x-layouts.app>
