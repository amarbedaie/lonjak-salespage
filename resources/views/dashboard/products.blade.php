<x-layouts.app title="Pustaka Produk">
    @php $rm = fn ($n) => 'RM'.number_format($n, 2); @endphp
    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <x-ui.page-header title="Pustaka Produk" description="{{ $products->count() }} produk · simpan info, gambar & video — jana salespage satu klik." />
            <x-ui.button :href="route('products.create')"><x-lucide-plus class="size-4" /> Produk baru</x-ui.button>
        </div>

        @if (session('ok'))<x-ui.card class="bg-success-soft/40"><x-ui.card-body class="text-sm text-success">✅ {{ session('ok') }}</x-ui.card-body></x-ui.card>@endif

        @if ($products->isEmpty())
            <x-ui.card>
                <x-ui.card-body class="flex flex-col items-center gap-3 py-14 text-center">
                    <div class="flex size-14 items-center justify-center rounded-full bg-primary-soft text-primary"><x-lucide-package class="size-7" /></div>
                    <div><h3 class="font-semibold text-ink">Pustaka anda kosong</h3><p class="mt-1 text-sm text-muted">Simpan produk pertama — info, gambar & video. Lepas tu jana salespage terus daripadanya.</p></div>
                    <x-ui.button :href="route('products.create')" class="mt-1"><x-lucide-plus class="size-4" /> Tambah produk pertama</x-ui.button>
                </x-ui.card-body>
            </x-ui.card>
        @else
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $p)
                    <x-ui.card class="flex flex-col overflow-hidden">
                        <div class="relative aspect-[16/10] w-full overflow-hidden bg-muted-surface">
                            @if ($p->thumbnailUrl())
                                <img src="{{ $p->thumbnailUrl() }}" class="size-full object-cover" alt="{{ $p->name }}">
                            @else
                                <div class="flex size-full items-center justify-center text-5xl">{{ $p->image }}</div>
                            @endif
                            @if (is_array($p->images) && count($p->images) > 1)
                                <span class="absolute bottom-2 right-2 rounded-full bg-black/60 px-2 py-0.5 text-[11px] text-white">+{{ count($p->images) - 1 }} gambar</span>
                            @endif
                            @if ($p->video_url)<span class="absolute left-2 top-2 flex items-center gap-1 rounded-full bg-black/60 px-2 py-0.5 text-[11px] text-white"><x-lucide-play class="size-3" /> video</span>@endif
                        </div>
                        <div class="flex flex-1 flex-col p-4">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-medium text-ink">{{ $p->name }}</h3>
                                <x-ui.status-pill :status="$p->status" />
                            </div>
                            @if ($p->category)<p class="mt-0.5 text-xs text-muted">{{ $p->category }}</p>@endif
                            <div class="mt-2 flex items-baseline gap-2">
                                <span class="text-lg font-semibold text-ink tnum">{{ $rm($p->price) }}</span>
                                @if ($p->compare_price > 0)<span class="text-sm text-muted line-through tnum">{{ $rm($p->compare_price) }}</span>@endif
                            </div>
                            <div class="mt-4 flex items-center gap-2">
                                <x-ui.button :href="route('salespages.create', ['product' => $p->id])" class="flex-1 justify-center"><x-lucide-sparkles class="size-4" /> Jana Salespage</x-ui.button>
                                <a href="{{ route('products.edit', $p) }}" class="flex size-9 items-center justify-center rounded-[var(--radius-md)] border border-border text-ink-soft hover:bg-muted-surface" title="Edit"><x-lucide-pencil class="size-4" /></a>
                                <form method="POST" action="{{ route('products.destroy', $p) }}" onsubmit="return confirm('Padam {{ $p->name }}?')">@csrf @method('DELETE')
                                    <button type="submit" class="flex size-9 items-center justify-center rounded-[var(--radius-md)] border border-border text-ink-soft hover:bg-danger-soft hover:text-danger" title="Padam"><x-lucide-trash-2 class="size-4" /></button>
                                </form>
                            </div>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
