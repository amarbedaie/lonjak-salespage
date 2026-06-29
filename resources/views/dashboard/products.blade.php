<x-layouts.app title="Produk">
    @php $rm = fn ($n) => 'RM'.number_format($n, 2); @endphp
    <div class="space-y-6">
        <x-ui.page-header title="Produk" description="{{ $products->count() }} produk" />

        <x-ui.card>
            <x-ui.card-header title="Tambah produk" subtitle="Simpan produk untuk guna dalam salespage & order" />
            <x-ui.card-body>
                @if (session('ok'))<p class="mb-3 rounded-[var(--radius-md)] border border-success/30 bg-success-soft px-3 py-2 text-sm text-success">{{ session('ok') }}</p>@endif
                <form method="POST" action="{{ route('products.store') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">@csrf
                    <x-ui.field label="Nama" class="lg:col-span-2"><x-ui.input name="name" placeholder="cth. Serum Glow" required /></x-ui.field>
                    <x-ui.field label="Harga (RM)"><x-ui.input name="price" type="number" placeholder="89" required /></x-ui.field>
                    <x-ui.field label="Kos (RM)"><x-ui.input name="cost" type="number" placeholder="28" /></x-ui.field>
                    <x-ui.field label="Stok"><x-ui.input name="stock" type="number" placeholder="100" /></x-ui.field>
                    <x-ui.button type="submit" class="lg:col-span-5 sm:w-fit"><x-lucide-plus class="size-4" /> Tambah produk</x-ui.button>
                </form>
            </x-ui.card-body>
        </x-ui.card>

        @if ($products->isNotEmpty())
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $p)
                    @php $margin = $p->price > 0 ? round(($p->price - $p->cost) / $p->price * 100) : 0; @endphp
                    <x-ui.card>
                        <div class="flex items-start gap-4 p-5">
                            <div class="flex size-14 shrink-0 items-center justify-center rounded-[var(--radius-md)] bg-muted-surface text-3xl">{{ $p->image }}</div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2"><h3 class="truncate font-medium text-ink">{{ $p->name }}</h3><x-ui.status-pill :status="$p->status" /></div>
                                @if ($p->sku)<p class="mt-0.5 font-mono text-xs text-muted">{{ $p->sku }}</p>@endif
                                <div class="mt-2 flex items-baseline gap-2"><span class="text-lg font-semibold text-ink tnum">{{ $rm($p->price) }}</span><x-ui.badge :tone="$margin >= 60 ? 'success' : 'warning'">{{ $margin }}% margin</x-ui.badge></div>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 divide-x divide-border border-t border-border text-center">
                            <div class="px-2 py-2.5"><p class="text-xs text-muted">Stok</p><p class="text-sm font-medium tnum {{ $p->stock == 0 ? 'text-danger' : 'text-ink' }}">{{ $p->stock }}</p></div>
                            <div class="px-2 py-2.5"><p class="text-xs text-muted">Terjual</p><p class="text-sm font-medium text-ink tnum">{{ number_format($p->sold) }}</p></div>
                            <div class="px-2 py-2.5"><p class="text-xs text-muted">Kos</p><p class="text-sm font-medium text-ink tnum">{{ $rm($p->cost) }}</p></div>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
