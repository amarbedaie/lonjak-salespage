<x-layouts.admin title="Order">
    @php $rm = fn ($n, $c = false) => $c && $n >= 1000 ? 'RM'.number_format($n / 1000, 1).'k' : 'RM'.number_format($n, 2);
    $filters = ['all' => 'Semua', 'baru' => 'Baru', 'diproses' => 'Diproses', 'dihantar' => 'Dihantar', 'selesai' => 'Selesai', 'batal' => 'Batal']; @endphp
    <div class="space-y-6">
        <x-ui.page-header title="Semua order" description="{{ $orders->count() }} order · GMV {{ $rm($gmv, true) }}" />
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="inline-flex items-center gap-0.5 rounded-[var(--radius-md)] border border-border bg-muted-surface p-0.5">
                @foreach ($filters as $k => $lbl)<a href="{{ route('admin.orders', ['status' => $k, 'q' => $q]) }}" class="h-7 inline-flex items-center rounded-[6px] px-3 text-xs font-medium transition-colors {{ $filter === $k ? 'bg-bg text-ink shadow-[0_1px_2px_rgba(0,0,0,0.06)]' : 'text-muted hover:text-ink' }}">{{ $lbl }}</a>@endforeach
            </div>
            <form method="GET" class="relative max-w-xs flex-1"><input type="hidden" name="status" value="{{ $filter }}"><x-lucide-search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted" /><x-ui.input name="q" value="{{ $q }}" class="pl-9" placeholder="Cari pelanggan / produk…" /></form>
        </div>
        @if ($orders->isEmpty())
            <x-ui.empty-state icon="shopping-cart" title="Belum ada order" description="Order dari semua merchant akan muncul di sini." />
        @else
            <x-ui.card class="overflow-hidden">
                <div class="overflow-x-auto scroll-thin"><table class="w-full text-sm">
                    <thead><tr class="border-b border-border bg-surface text-left text-xs text-muted"><th class="px-5 py-3 font-medium">Pelanggan</th><th class="px-5 py-3 font-medium">Produk</th><th class="px-5 py-3 font-medium">Negeri</th><th class="px-5 py-3 text-right font-medium">Jumlah</th><th class="px-5 py-3 font-medium">Status</th><th class="px-5 py-3 font-medium">Tarikh</th></tr></thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($orders as $o)
                            <tr class="hover:bg-muted-surface/50"><td class="px-5 py-3"><div class="font-medium text-ink">{{ $o->customer }}</div><div class="text-xs text-muted">{{ $o->phone }}</div></td><td class="px-5 py-3 text-ink-soft">{{ $o->product_name }}</td><td class="px-5 py-3 text-ink-soft">{{ $o->state ?? '—' }}</td><td class="px-5 py-3 text-right font-medium text-ink tnum">{{ $rm($o->total) }}</td><td class="px-5 py-3"><x-ui.status-pill :status="$o->status" /></td><td class="px-5 py-3 text-xs text-muted">{{ $o->created_at->translatedFormat('d M, h:i A') }}</td></tr>
                        @endforeach
                    </tbody>
                </table></div>
            </x-ui.card>
        @endif
    </div>
</x-layouts.admin>
