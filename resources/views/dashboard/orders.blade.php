<x-layouts.app title="Order">
    @php $rm = fn ($n) => 'RM'.number_format($n, 2);
    $filters = ['all' => 'Semua', 'baru' => 'Baru', 'diproses' => 'Diproses', 'dihantar' => 'Dihantar', 'selesai' => 'Selesai', 'batal' => 'Batal']; @endphp
    <div class="space-y-6">
        <x-ui.page-header title="Order" description="{{ $orders->count() }} order · {{ $rm($total) }} jumlah" />

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="inline-flex items-center gap-0.5 rounded-[var(--radius-md)] border border-border bg-muted-surface p-0.5">
                @foreach ($filters as $k => $lbl)
                    <a href="{{ route('orders.index', ['status' => $k, 'q' => $q]) }}"
                       class="h-7 inline-flex items-center rounded-[6px] px-3 text-xs font-medium transition-colors {{ $filter === $k ? 'bg-bg text-ink shadow-[0_1px_2px_rgba(0,0,0,0.06)]' : 'text-muted hover:text-ink' }}">{{ $lbl }}</a>
                @endforeach
            </div>
            <form method="GET" class="relative max-w-xs flex-1">
                <input type="hidden" name="status" value="{{ $filter }}">
                <x-lucide-search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted" />
                <x-ui.input name="q" value="{{ $q }}" class="pl-9" placeholder="Cari nama / telefon…" />
            </form>
        </div>

        @if ($orders->isEmpty())
            <x-ui.empty-state icon="filter" title="Tiada order" description="Order akan masuk automatik bila pelanggan checkout di salespage live anda." />
        @else
            <x-ui.card class="overflow-hidden">
                <div class="overflow-x-auto scroll-thin">
                    <table class="w-full text-sm">
                        <thead><tr class="border-b border-border bg-surface text-left text-xs text-muted">
                            <th class="px-4 py-3 font-medium">Pelanggan</th><th class="px-4 py-3 font-medium">Produk</th><th class="px-4 py-3 font-medium">Negeri</th>
                            <th class="px-4 py-3 text-right font-medium">Jumlah</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 font-medium">Tarikh</th>
                        </tr></thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($orders as $o)
                                <tr class="transition-colors hover:bg-muted-surface/50">
                                    <td class="px-4 py-3"><div class="font-medium text-ink">{{ $o->customer }}</div><div class="text-xs text-muted">{{ $o->phone }}</div></td>
                                    <td class="px-4 py-3 text-ink-soft">{{ $o->product_name }} @if ($o->qty > 1)<span class="text-muted">×{{ $o->qty }}</span>@endif</td>
                                    <td class="px-4 py-3 text-ink-soft">{{ $o->state ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-ink tnum">{{ $rm($o->total) }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <x-ui.status-pill :status="$o->status" />
                                            <form method="POST" action="{{ route('orders.status', $o) }}">@csrf
                                                <select name="status" onchange="this.form.submit()" class="h-7 rounded-md border border-border bg-bg px-1.5 text-xs text-ink-soft focus:border-primary focus:outline-none">
                                                    @foreach (['baru', 'diproses', 'dihantar', 'selesai', 'batal'] as $st)<option value="{{ $st }}" @selected($o->status === $st)>{{ $st }}</option>@endforeach
                                                </select>
                                            </form>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-muted">{{ $o->created_at->translatedFormat('d M, h:i A') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        @endif
    </div>
</x-layouts.app>
