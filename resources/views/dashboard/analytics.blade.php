<x-layouts.app title="Analitik">
    @php $rm = fn ($n, $c = false) => $c && $n >= 1000 ? 'RM'.number_format($n / 1000, 1).'k' : 'RM'.number_format($n, 2);
    $maxStatus = max(1, $statusCounts->max('n')); @endphp
    <div class="space-y-6">
        <x-ui.page-header title="Analitik" description="Prestasi jualan dari data sebenar." />

        @if ($orders->isEmpty() && $top->isEmpty())
            <x-ui.empty-state icon="bar-chart-3" title="Belum ada data" description="Analitik akan muncul bila ada salespage & order." />
        @else
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <x-ui.metric label="Jumlah jualan" :value="$rm($revenue, true)" />
                <x-ui.metric label="Purata nilai order" :value="$rm($aov)" />
                <x-ui.metric label="Jumlah order" :value="number_format($orders->count())" />
                <x-ui.metric label="Kadar conversion" :value="number_format($cr, 1).'%'" :footnote="number_format($visits).' pelawat'" />
            </div>

            <x-ui.card>
                <x-ui.card-header title="Jualan" subtitle="14 hari" />
                <x-ui.card-body><x-ui.area-chart :data="$series" :height="260" id="an" /></x-ui.card-body>
            </x-ui.card>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-ui.card>
                    <x-ui.card-header title="Pecahan status order" />
                    <x-ui.card-body class="space-y-3">
                        @foreach ($statusCounts as $s)
                            <div>
                                <div class="flex items-center justify-between text-sm"><span class="text-ink-soft">{{ $s['label'] }}</span><span class="font-medium text-ink tnum">{{ $s['n'] }}</span></div>
                                <div class="mt-1 h-2 overflow-hidden rounded-full bg-muted-surface"><div class="h-full rounded-full bg-primary" style="width: {{ $s['n'] / $maxStatus * 100 }}%"></div></div>
                            </div>
                        @endforeach
                    </x-ui.card-body>
                </x-ui.card>
                <x-ui.card>
                    <x-ui.card-header title="Salespage ikut jualan" />
                    <x-ui.card-body class="p-0">
                        @if ($top->isEmpty())
                            <p class="px-5 py-8 text-center text-sm text-muted">Belum ada salespage dengan trafik.</p>
                        @else
                            <div class="overflow-x-auto scroll-thin"><table class="w-full text-sm">
                                <thead><tr class="border-b border-border text-left text-xs text-muted"><th class="px-5 py-2.5 font-medium">Salespage</th><th class="px-5 py-2.5 text-right font-medium">Pelawat</th><th class="px-5 py-2.5 text-right font-medium">Order</th><th class="px-5 py-2.5 text-right font-medium">Jualan</th></tr></thead>
                                <tbody class="divide-y divide-border">
                                    @foreach ($top as $p)
                                        <tr class="hover:bg-muted-surface/50"><td class="px-5 py-3 font-medium text-ink">{{ $p->title }}</td><td class="px-5 py-3 text-right text-ink-soft tnum">{{ number_format($p->visits) }}</td><td class="px-5 py-3 text-right text-ink-soft tnum">{{ $p->ord }}</td><td class="px-5 py-3 text-right font-medium text-ink tnum">{{ $rm($p->rev, true) }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table></div>
                        @endif
                    </x-ui.card-body>
                </x-ui.card>
            </div>
        @endif
    </div>
</x-layouts.app>
