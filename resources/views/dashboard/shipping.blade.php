<x-layouts.app title="Penghantaran">
    <div class="space-y-6">
        <x-ui.page-header title="Penghantaran" description="Sambung kurier untuk jana AWB & tracking automatik." />
        <x-ui.card class="bg-info-soft/40">
            <x-ui.card-body class="text-sm text-ink-soft">📦 Uruskan penghantaran secara manual sekarang (kemas kini status order + no. tracking di Order). Sambung kurier dengan kunci API untuk <strong class="text-ink">jana AWB & cetak label automatik</strong>.</x-ui.card-body>
        </x-ui.card>
        <x-ui.card>
            <x-ui.card-header title="Kurier" subtitle="Masukkan kunci API untuk aktifkan AWB automatik" />
            <x-ui.card-body class="divide-y divide-border p-0">
                @foreach ([['EP', 'EasyParcel', 'Agregator — banyak kurier, satu akaun'], ['JT', 'J&T Express', 'Pickup harian, jejak automatik'], ['PL', 'Pos Laju', 'Liputan seluruh Malaysia'], ['NV', 'NinjaVan', 'COD & next-day delivery']] as [$logo, $name, $desc])
                    <div class="flex items-center gap-4 px-5 py-4">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-[var(--radius-md)] border border-border bg-bg text-sm font-bold tracking-tight text-ink">{{ $logo }}</div>
                        <div class="min-w-0 flex-1"><h3 class="font-medium text-ink">{{ $name }}</h3><p class="mt-0.5 text-sm text-muted">{{ $desc }}</p></div>
                        <x-ui.button size="sm">Sambung</x-ui.button>
                    </div>
                @endforeach
            </x-ui.card-body>
        </x-ui.card>
        <p class="px-1 text-xs text-muted">COD disokong untuk kawasan layak setelah kurier disambung.</p>
    </div>
</x-layouts.app>
